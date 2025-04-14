<?php

namespace App\GraphQL\Queries;

use App\Models\Account;
use App\Models\LoLMatch;
use App\Models\Summoner;
use Carbon\Carbon;
use Closure;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class AccountQuery extends Query
{
    const SOLO_QUEUE = 420;
    const MATCHES_BATCH = 10;
    const REFRESH_LIMIT_MINUTES = 10;

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
            ],
            'tag' => [
                'type' => Type::string(),
                'description' => 'The account tag',
            ],
        ];
    }

    /**
     * @throws Error
     */
    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        if (isset($args['name'], $args['tag'])) {
            $account = Account::firstWhere((
                ['name' => $args['name'], 'tag' => $args['tag']]
            ));
            if (!$account || now()->diffInMinutes($account->refreshed_at) > self::REFRESH_LIMIT_MINUTES) {
                $client = Http::acceptJson()->withHeaders([
                    'X-Riot-Token' => config('riot.apikey')
                ]);
                try {
                    Log::info('Miaou2');
                    $account_response = $client->withUrlParameters([
                        'region' => 'europe',
                        'name' => $args['name'],
                        'tag' => $args['tag']
                    ])->get('https://{region}.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{name}/{tag}')->json();

                    Log::info($account_response);

                    $summoner_response = $client->withUrlParameters([
                        'region' => 'euw1',
                        'puuid' => $account_response['puuid'],
                    ])->get('https://{region}.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/{puuid}')->json();
                    Log::info($summoner_response);

                    $matchids_response = $client->withUrlParameters([
                        'region' => 'europe',
                        'puuid' => $account_response['puuid'],
                    ])->withQueryParameters([
                        'queue' => self::SOLO_QUEUE,
                        'count' => self::MATCHES_BATCH,
                    ])->get('https://{region}.api.riotgames.com/lol/match/v5/matches/by-puuid/{puuid}/ids')->json();

                    Log::info($matchids_response);

                    $matches_response = Http::pool(function (Pool $pool) use ($matchids_response) {
                        $new_pool = $pool
                            ->withHeaders(['X-Riot-Token' => config('riot.apikey')])
                            ->acceptJson();

                        return array_map(
                            fn($match) => $new_pool->get("https://europe.api.riotgames.com/lol/match/v5/matches/$match"),
                            $matchids_response
                        );
                    });

                    $account = Account::updateOrCreate([
                        'puuid' => $account_response['puuid'],
                        'name' => $args['name'],
                        'tag' => $args['tag'],
                        'refreshed_at' => now(),
                    ]);

                    $summoner = $account->summoner()->create([
                        'id' => $summoner_response['id'],
                        'icon' => $summoner_response['profileIconId'],
                        'revision_date' => Carbon::createFromTimestampMs($summoner_response['revisionDate']),
                        'level' => $summoner_response['summonerLevel'],
                    ]);

                    foreach ($matches_response as $match) {

                        $lolmatch = LoLMatch::create([
                            'id' => $match['metadata']['matchId'],
                            'duration' => $match['info']['gameDuration'],
                            'game_creation' => Carbon::createFromTimestampMs($match['info']['gameCreation']),
                        ]);


                        foreach ($match['info']['participants'] as $participant) {
                            $participantSummoner = Summoner::firstOrCreate(
                                ['id' => $participant['summonerId']],
                            );

                            // Attach the participant to the match via the pivot table
                            $participantSummoner->lolmatches()->syncWithoutDetaching([
                                $lolmatch->id => [
                                    'champion_id' => $participant['championId'],
                                    'team_id' => $participant['teamId'],
                                    'team_position' => $participant['teamPosition'],
                                    'win' => $participant['win'],
                                    'kills' => $participant['kills'],
                                    'deaths' => $participant['deaths'],
                                    'assists' => $participant['assists'],
                                    'level' => $participant['champLevel'],
                                ]
                            ]);
                        }

                    }

                } catch (ConnectionException $e) {
                    Log::error($e->getMessage());
                }

            }
            return $account;
        }
        else {
            throw new Error('Account not found');
        }
    }
}
