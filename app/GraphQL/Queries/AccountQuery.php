<?php

namespace App\GraphQL\Queries;

use App\Models\Account;
use App\Models\LoLMatch;
use App\Models\Participant;
use Carbon\Carbon;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class AccountQuery extends Query
{
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
        return Http::pool(fn (Pool $pool) => array_map(
                fn($match) => $pool->withHeaders(['X-Riot-Token' => config('riot.apikey')])
                    ->acceptJson()->get("https://europe.api.riotgames.com/lol/match/v5/matches/$match"),
                $matchids)
        );
    }

    private function createCompleteMatches(array $matches, string $puuid)
    {
        $return_account = null;
        foreach ($matches as $match) {

            $lolmatch = LoLMatch::firstOrCreate([
                'id' => $match['metadata']['matchId']
            ],
                [
                    'duration' => $match['info']['gameDuration'],
                    'game_creation' => Carbon::createFromTimestampMs($match['info']['gameCreation']),
                ]
            );

            Log::debug($lolmatch);

            foreach ($match['info']['participants'] as $participant) {
                Log::debug($participant);
                $account = Account::firstOrNew(
                    [ 'puuid' => $participant['puuid']],
                    [
                        'name' => $participant['riotIdGameName'],
                        'tag' => $participant['riotIdTagline'],
                        'refreshed_at' => now(),
                    ]
                );
                Log::debug($account);
                $summoner = $account->summoner()->first();
                $summoner ??= $account->summoner()->create(
                    [
                        'id' => $participant['summonerId'],
                        'icon' => $participant['profileIcon'],
                        'level' => $participant['summonerLevel'],
                    ],
                );
                Log::debug($summoner);
                $participant_model = Participant::make(
                    [
                        'champion_id' => $participant['championId'],
                        'team_id' => $participant['teamId'],
                        'team_position' => $participant['teamPosition'],
                        'win' => $participant['win'],
                        'kills' => $participant['kills'],
                        'deaths' => $participant['deaths'],
                        'assists' => $participant['assists'],
                        'level' => $participant['champLevel'],
                    ]
                );
                $participant_model->lolmatch()->associate($lolmatch);
                $participant_model->summoner()->associate($summoner);
                $participant_model->save();
                if ($puuid == $account->puuid)
                {
                    $return_account = $account;
                }
            }
        }
        return $return_account;
    }

    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        /** @var SelectFields $fields */
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $account = Account::select($select)->with($with)->firstWhere((
            ['name' => $args['name'], 'tag' => $args['tag']]
        ));
        if (!$account || now()->diffInMinutes($account->refreshed_at) > config("riot.refreshlimit")) {
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
            $account = Account::select($select)->with($with)->find($account->id);
        }
        return $account;
    }
}
