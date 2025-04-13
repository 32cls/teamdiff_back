<?php

namespace App\GraphQL\Queries;

use App\Models\Account;
use App\Models\Summoner;
use Closure;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class AccountQuery extends Query
{
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
            if (!$account) {
                throw new Error('Account not found');
            }
            return $account;
        }
        throw new Error('Account not found');
    }
}
