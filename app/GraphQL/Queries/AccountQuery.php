<?php

namespace App\GraphQL\Queries;

use App\GraphQL\Traits\RateLimited;
use App\Models\Account;
use App\Models\LoLMatch;
use App\Models\Participation;
use App\Models\Summoner;
use Carbon\Carbon;
use Closure;
use Exception;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class AccountQuery extends Query
{
    use RateLimited;
    private $client;

    public function __construct()
    {
        $this->client = Http::acceptJson()->withHeaders([
            'X-Riot-Token' => config('riot.apikey')
        ]);
    }

    protected $attributes = [
        'name' => 'account',
    ];

    public function type(): Type
    {
        return GraphQL::type("Account");
    }

    public function args(): array
    {
        return [
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The account name',
                'rules' => ['required', 'min:3', 'max:16'],
            ],
            'tag' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The account tag',
                'rules' => ['required', 'min:3', 'max:5'],
            ],
            'region' => [
                'type' => Type::nonNull(GraphQL::type('RegionEnum')),
                'description' => 'The account region',
                'rules' => ['required'],
            ]
        ];
    }

    /**
     * @throws Error
     */
    private function fetchAccount(string $name, string $tag)
    {
        try {
             return $this->client->withUrlParameters([
                'name' => $name,
                'tag' => $tag
            ])->get('https://europe.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{name}/{tag}')
                ->throw()
                ->json();
        } catch (ConnectionException | RequestException $e) {
            throw new Error("Failed to fetch account : {$e->getMessage()}");
        }
    }

    /**
     * @throws Error
     */
    private function fetchSummoner(string $puuid, string $region)
    {
        try {
            return $this->client->withUrlParameters([
                'region' => strtolower($region),
                'puuid' => $puuid
            ])->get('https://{region}.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/{puuid}')
                ->throw()
                ->json();
        } catch (ConnectionException | RequestException $e) {
            throw new Error("Failed to fetch summoner : {$e->getMessage()}");
        }
    }

    /**
     * @throws Error
     */
    private function fetchMatchesId(string $puuid, string $region)
    {
        Log::debug($region);
        try {
            return $this->client->withUrlParameters([
                'region' => $this->continent($region),
                'puuid' => $puuid,
            ])->withQueryParameters([
                'queue' => config("riot.queue"),
                'count' => config("riot.matchesbatch"),
            ])->get('https://{region}.api.riotgames.com/lol/match/v5/matches/by-puuid/{puuid}/ids')
                ->throw()
                ->json();
        } catch (ConnectionException | RequestException $e) {
            throw new Error("Failed to fetch match ids : {$e->getMessage()}");
        }
    }

    private function fetchMatches(array $matchids, string $region): array
    {
        $found = LoLMatch::whereIn('id', $matchids)->get();
        $missingMatches = collect($matchids)->diff($found)->toArray();
        $pool = Http::pool(fn (Pool $pool) => array_map(
                fn($match) => $pool
                            ->withHeaders(['X-Riot-Token' => config('riot.apikey')])
                            ->acceptJson()
                            ->get("https://{$this->continent($region)}.api.riotgames.com/lol/match/v5/matches/$match"),
                $missingMatches)
        );
        foreach ($pool as $response) {
            $response->throw();
        }
        return $pool;
    }

    private function upsertMatches(Collection $matchIds, array $matches): void
    {
        $existingMatches = LoLMatch::whereIn('id', $matchIds)->pluck('id')->all();
        $newMatches = collect($matches)
            ->filter(fn($match) => !in_array($match['metadata']['matchId'], $existingMatches))
            ->map(fn($match) => [
                'id' => $match['metadata']['matchId'],
                'duration' => $match['info']['gameDuration'],
                'gameCreation' => Carbon::createFromTimestampMs($match['info']['gameCreation']),
            ])
            ->unique('id')   // Ensure no duplicate match IDs
            ->values()
            ->all();

        if (!empty($newMatches)) {
            LoLMatch::upsert($newMatches, ['id'], ['duration', 'gameCreation']);
        }
    }

    private function upsertAccounts(Collection $puuids, Collection $participations, string $puuid, string $region): void
    {
        $existingAccounts = Account::whereIn('puuid', $puuids)->get()->keyBy('puuid');

        $newAccounts = $puuids
            ->diff($existingAccounts->keys())
            ->map(fn($mapPuuid) => [
                'puuid' => $mapPuuid,
                'name' => optional($participations->firstWhere('puuid', $mapPuuid))['riotIdGameName'],
                'tag' => optional($participations->firstWhere('puuid', $mapPuuid))['riotIdTagline'],
                'refreshedAt' => $mapPuuid == $puuid ? now() : null,
                'region' => $region
            ])
            ->unique('puuid')  // Prevent duplicate puuids
            ->values()
            ->all();

        if (!empty($newAccounts)) {
            Account::upsert($newAccounts, ['puuid'], ['name', 'tag', 'refreshedAt', 'region']);
        }

    }

    private function upsertSummoners(Collection $participations, Collection $existingSummoners): void
    {
        $newSummoners = $participations
            ->filter(fn($p) => !isset($existingSummoners[$p['summonerId']]))
            ->map(fn($p) => [
                'id' => $p['summonerId'],
                'accountId' => $p['puuid'],
                'icon' => $p['profileIcon'],
                'level' => $p['summonerLevel'],
            ])
            ->unique('id')  // Avoid duplicate summoner IDs
            ->values()
            ->all();

        if (!empty($newSummoners)) {
            Summoner::upsert($newSummoners, ['id'], ['accountId', 'icon', 'level']);
            $accountIds = collect($newSummoners)->pluck('accountId')->unique();
            Account::whereIn('puuid', $accountIds)->searchable();
        }
    }

    private function upsertParticipations(array $matches, Collection $matchesById, Collection $accounts, Collection $summoners, string $puuid): Account
    {
        $participationData = collect($matches)->flatMap(function ($match) use ($matchesById, $accounts, $summoners, $puuid, &$returnAccount) {
            return collect($match['info']['participants'])->map(function ($p) use ($match, $matchesById, $accounts, $summoners, $puuid, &$returnAccount) {
                $account = $accounts[$p['puuid']] ?? null;
                $summoner = $summoners[$p['summonerId']] ?? null;
                $lolmatch = $matchesById[$match['metadata']['matchId']] ?? null;

                if ($account && $puuid === $account->puuid) {
                    $returnAccount = $account;
                }

                return [
                    'matchId' => $lolmatch->id ?? null,
                    'summonerId' => $summoner->id ?? null,
                    'championId' => $p['championName'],
                    'teamId' => $p['teamId'],
                    'role' => $p['teamPosition'] == 'UTILITY' ? 'SUPPORT' : $p['teamPosition'],
                    'win' => $p['win'],
                    'kills' => $p['kills'],
                    'deaths' => $p['deaths'],
                    'assists' => $p['assists'],
                    'level' => $p['champLevel'],
                ];
            });
        })
            ->filter(fn($p) => $p['matchId'] !== null && $p['summonerId'] !== null)  // Remove any null values
            ->unique(fn($p) => $p['matchId'] . '-' . $p['summonerId']) // Ensure uniqueness
            ->values()
            ->all();

        // Perform the upsert for all participations at once
        if (!empty($participationData)) {
            Participation::upsert($participationData, ['matchId', 'summonerId'], [
                'championId',
                'teamId',
                'role',
                'win',
                'kills',
                'deaths',
                'assists',
                'level',
            ]);
        }
        return $returnAccount;
    }
    private function createCompleteMatches(array $matches, string $puuid, string $region): Account
    {
        // === Phase 1: Upsert Matches ===
        $matchIds = collect($matches)->pluck('metadata.matchId')->unique();
        $this->upsertMatches($matchIds, $matches);

        $matchesById = LoLMatch::whereIn('id', $matchIds)->get()->keyBy('id');

        // === Phase 2: Upsert Accounts ===
        $participations = collect($matches)->flatMap(fn($match) => $match['info']['participants']);
        $puuids = $participations->pluck('puuid')->unique();
        Log::debug($puuids);
        $this->upsertAccounts($puuids, $participations, $puuid, $region);
        $accounts = Account::whereIn('puuid', $puuids)->get()->keyBy('puuid');

        // === Phase 3: Upsert Summoners ===
        $summonerIds = $participations->pluck('summonerId')->unique();
        $existingSummoners = Summoner::whereIn('id', $summonerIds)->get()->keyBy('id');
        $this->upsertSummoners($participations, $existingSummoners);

        $summoners = Summoner::whereIn('id', $summonerIds)->get()->keyBy('id');

        // === Phase 4: Insert/Upsert participations ===
        // Gather all participations in a single collection
        return $this->upsertParticipations($matches, $matchesById, $accounts, $summoners, $puuid);
    }

    private function continent(string $region): string
    {
        return match ($region) {
            'NA1', 'BR1', 'LA1', 'LA2' => 'americas',
            'KR', 'JP1' => 'asia',
            'EUN1', 'EUW1', 'ME1', 'TR1', 'RU' => 'europe',
            'OC1', 'SG2', 'TW2', 'VN2' => 'sea',
            default => '',
        };
    }

    /**
     * @throws Exception
     */
    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $this->enforceRateLimit('AccountQuery', 20);

        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $region = $args['region'];

        $account = Account::select($select)->with($with)->firstWhere((
            ['name' => $args['name'], 'tag' => $args['tag'], 'region' => $region]
        ));
        if (!$account || !$account->refreshedAt || now()->diffInMinutes($account->refreshedAt) > config("riot.refreshlimit")) {
            $accountResponse = $this->fetchAccount($args['name'], $args['tag']);

            $puuid = $accountResponse['puuid'];

            Log::debug($accountResponse);

            $summonerResponse = $this->fetchSummoner($puuid, $region);

            Log::debug($summonerResponse);
            Log::debug($region);
            $matchidsResponse = $this->fetchMatchesId($puuid, $region);

            Log::debug($matchidsResponse);

            $matchesResponse = $this->fetchMatches($matchidsResponse, $region);
            Log::debug(implode($matchesResponse));

            $account = $this->createCompleteMatches($matchesResponse, $puuid, $region);
            $account = Account::select($select)->with($with)->find($account->puuid);
        }
        return $account;
    }
}
