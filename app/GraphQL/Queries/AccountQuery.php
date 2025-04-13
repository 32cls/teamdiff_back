<?php

namespace App\GraphQL\Queries;

use App\Models\Account;
use App\Models\Summoner;
use Closure;
use GraphQL\Error\Error;
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

                    /** @var array */
                    $matchids_response = $client->withUrlParameters([
                        'region' => 'europe',
                        'puuid' => $account_response['puuid'],
                    ])->withQueryParameters([
                        'queue' => self::SOLO_QUEUE,
                        'count' => self::MATCHES_BATCH,
                    ])->get('https://{region}.api.riotgames.com/lol/match/v5/matches/by-puuid/{puuid}/ids')->json();

                    Log::info($matchids_response);

                    $matches_response = Http::pool(fn (Pool $pool) => [
                        $pool->get('http://localhost/first'),
                        $pool->get('http://localhost/second'),
                        $pool->get('http://localhost/third'),
                    ]);

                    $summoner = Summoner::create([
                        'icon' => $account_response['profileIconId'],
                        'revision_date' => $account_response['revisionDate'],
                        'level' => $account_response['summonerLevel'],
                    ]);

                    $account = Account::updateOrCreate([
                        'puuid' => $account_response['puuid'],
                        'name' => $args['name'],
                        'tag' => $args['tag'],
                        'refreshed_at' => now(),
                    ]);

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
