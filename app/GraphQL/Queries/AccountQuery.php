<?php

namespace App\GraphQL\Queries;

use App\Models\Account;
use App\Models\Summoner;
use Closure;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GuzzleHttp\Pool;
use Illuminate\Support\Facades\Http;
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
                    'X-Riot-Token' => config('riot.api.key')
                ]);
                $account_response = $client->withUrlParameters([
                    'region' => 'europe',
                    'name' => $args['name'],
                    'tag' => $args['tag']
                ])->get('https://{region}.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{name}/{tag}')->json();
                $summoner_response = $client->withUrlParameters([
                    'region' => 'euw',
                    'puuid' => $account_response['puuid'],
                ])->get('https://{region}.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/{puuid}')->json();

                /** @var array */
                $matchids_response = $client->withUrlParameters([
                    'region' => 'euw',
                    'puuid' => $account_response['puuid'],
                ])->withQueryParameters([
                    'queue' => self::SOLO_QUEUE,
                    'count' => self::MATCHES_BATCH,
                ])->get('https://{region}.api.riotgames.com/lol/match/v5/matches/by-puuid/{puuid}/ids')->json();

                $account = Account::create([
                    'puuid' => $account_response['puuid'],
                    'name' => $args['name'],
                    'tag' => $args['tag'],
                    'refreshed_at' => now(),
                ]);

            }
            return $account;
        }
        else {
            throw new Error('Account not found');
        }
    }
}
