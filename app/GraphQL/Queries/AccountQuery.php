<?php

namespace App\GraphQL\Queries;

use App\GraphQL\Traits\RateLimited;
use App\Models\Account;
use App\Models\LoLMatch;
use App\Models\Participant;
use App\Models\Summoner;
use Carbon\Carbon;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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
        'name' => 'fetch_account',
    ];

    public function type(): Type
    {
        return GraphQL::type("Account");
    }

    public function args(): array
    {
        return [
            'name' => [
                'type' => Type::string(),
                'description' => 'The account name',
                'rules' => ['required'],
            ],
            'tag' => [
                'type' => Type::string(),
                'description' => 'The account tag',
                'rules' => ['required'],
            ],
        ];
    }

    private function fetchAccount(string $name, string $tag)
    {
        try {
            return $this->client->withUrlParameters([
                'region' => 'europe',
                'name' => $name,
                'tag' => $tag
            ])->get('https://{region}.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{name}/{tag}')->json();
        } catch (ConnectionException $e) {
            Log::error("Failed to fetch account : {$e->getMessage()}");
            return null;
        }
    }

    private function fetchSummoner(string $puuid)
    {
        try {
            return $this->client->withUrlParameters([
                'region' => 'euw1',
                'puuid' => $puuid
            ])->get('https://{region}.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/{puuid}')->json();
        } catch (ConnectionException $e) {
            Log::error("Failed to fetch account : {$e->getMessage()}");
            return null;
        }
    }

    private function fetchMatchesId(string $puuid)
    {
        try {
            return $this->client->withUrlParameters([
                'region' => 'europe',
                'puuid' => $puuid,
            ])->withQueryParameters([
                'queue' => config("riot.queue"),
                'count' => config("riot.matchesbatch"),
            ])->get('https://{region}.api.riotgames.com/lol/match/v5/matches/by-puuid/{puuid}/ids')->json();
        } catch (ConnectionException $e) {
            Log::error("Failed to fetch account : {$e->getMessage()}");
            return null;
        }
    }

    private function fetchMatches(array $matchids): array
    {
        $found = LoLMatch::whereIn('id', $matchids)->get();
        Log::debug($found);
        $missing_matches = collect($matchids)->diff($found)->toArray();
        Log::debug(implode($missing_matches));
        return Http::pool(fn (Pool $pool) => array_map(
                fn($match) => $pool->withHeaders(['X-Riot-Token' => config('riot.apikey')])
                    ->acceptJson()->get("https://europe.api.riotgames.com/lol/match/v5/matches/$match"),
                $missing_matches)
        );
    }

    private function createCompleteMatches(array $matches, string $puuid): Account
    {
        $return_account = null;

        // === Phase 1: Upsert Matches ===
        $matchIds = collect($matches)->pluck('metadata.matchId')->unique();
        $existingMatches = LoLMatch::whereIn('id', $matchIds)->pluck('id')->all();
        $newMatches = collect($matches)
            ->filter(fn($match) => !in_array($match['metadata']['matchId'], $existingMatches))
            ->map(fn($match) => [
                'id' => $match['metadata']['matchId'],
                'duration' => $match['info']['gameDuration'],
                'game_creation' => Carbon::createFromTimestampMs($match['info']['gameCreation']),
            ])
            ->unique('id')   // Ensure no duplicate match IDs
            ->values()
            ->all();

        if (!empty($newMatches)) {
            LoLMatch::upsert($newMatches, ['id'], ['duration', 'game_creation']);
        }

        $matchesById = LoLMatch::whereIn('id', $matchIds)->get()->keyBy('id');

        // === Phase 2: Upsert Accounts ===
        $participants = collect($matches)->flatMap(fn($match) => $match['info']['participants']);
        $puuids = $participants->pluck('puuid')->unique();
        $existingAccounts = Account::whereIn('puuid', $puuids)->get()->keyBy('puuid');

        $newAccounts = $puuids
            ->diff($existingAccounts->keys())
            ->map(fn($map_puuid) => [
                'puuid' => $map_puuid,
                'name' => optional($participants->firstWhere('puuid', $map_puuid))['riotIdGameName'],
                'tag' => optional($participants->firstWhere('puuid', $map_puuid))['riotIdTagline'],
                'refreshed_at' => $map_puuid == $puuid ? now() : null,
            ])
            ->unique('puuid')  // Prevent duplicate puuids
            ->values()
            ->all();

        if (!empty($newAccounts)) {
            Account::upsert($newAccounts, ['puuid'], ['name', 'tag', 'refreshed_at']);
        }

        $accounts = Account::whereIn('puuid', $puuids)->get()->keyBy('puuid');

        // === Phase 3: Upsert Summoners ===
        $summonerIds = $participants->pluck('summonerId')->unique();
        $existingSummoners = Summoner::whereIn('id', $summonerIds)->get()->keyBy('id');

        $newSummoners = $participants
            ->filter(fn($p) => !isset($existingSummoners[$p['summonerId']]))
            ->map(fn($p) => [
                'id' => $p['summonerId'],
                'account_id' => $p['puuid'],
                'icon' => $p['profileIcon'],
                'level' => $p['summonerLevel'],
            ])
            ->unique('id')  // Avoid duplicate summoner IDs
            ->values()
            ->all();

        if (!empty($newSummoners)) {
            Summoner::upsert($newSummoners, ['id'], ['account_id', 'icon', 'level']);
            $account_ids = collect($newSummoners)->pluck('account_id')->unique();
            Account::whereIn('puuid', $account_ids)->searchable();
        }

        $summoners = Summoner::whereIn('id', $summonerIds)->get()->keyBy('id');

        // === Phase 4: Insert/Upsert Participants ===
        // Gather all participants in a single collection
        $participantData = collect($matches)->flatMap(function ($match) use ($matchesById, $accounts, $summoners, $puuid, &$return_account) {
            return collect($match['info']['participants'])->map(function ($p) use ($match, $matchesById, $accounts, $summoners, $puuid, &$return_account) {
                $account = $accounts[$p['puuid']] ?? null;
                $summoner = $summoners[$p['summonerId']] ?? null;
                $lolmatch = $matchesById[$match['metadata']['matchId']] ?? null;

                if ($account && $puuid === $account->puuid) {
                    $return_account = $account;
                }

                return [
                    'match_id' => $lolmatch->id ?? null,
                    'summoner_id' => $summoner->id ?? null,
                    'champion_id' => $p['championId'],
                    'team_id' => $p['teamId'],
                    'team_position' => $p['teamPosition'],
                    'win' => $p['win'],
                    'kills' => $p['kills'],
                    'deaths' => $p['deaths'],
                    'assists' => $p['assists'],
                    'level' => $p['champLevel'],
                ];
            });
        })
            ->filter(fn($p) => $p['match_id'] !== null && $p['summoner_id'] !== null)  // Remove any null values
            ->unique(fn($p) => $p['match_id'] . '-' . $p['summoner_id']) // Ensure uniqueness
            ->values()
            ->all();

        // Perform the upsert for all participants at once
        if (!empty($participantData)) {
            Participant::upsert($participantData, ['match_id', 'summoner_id'], [
                'champion_id',
                'team_id',
                'team_position',
                'win',
                'kills',
                'deaths',
                'assists',
                'level',
            ]);
        }
        return $return_account;
    }

    /**
     * @throws \Exception
     */
    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $this->enforceRateLimit('AccountQuery', 20);

        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $account = Account::select($select)->with($with)->firstWhere((
            ['name' => $args['name'], 'tag' => $args['tag']]
        ));
        if (!$account || !$account->refreshed_at || now()->diffInMinutes($account->refreshed_at) > config("riot.refreshlimit")) {
            $account_response = $this->fetchAccount($args['name'], $args['tag']);
            $puuid = $account_response['puuid'];

            Log::debug($account_response);

            $summoner_response = $this->fetchSummoner($puuid);

            Log::debug($summoner_response);

            $matchids_response = $this->fetchMatchesId($puuid);

            Log::debug($matchids_response);

            $matches_response = $this->fetchMatches($matchids_response);
            Log::debug(implode($matches_response));

            $account = $this->createCompleteMatches($matches_response, $puuid);
            $account = Account::select($select)->with($with)->find($account->puuid);
        }
        return $account;
    }
}
