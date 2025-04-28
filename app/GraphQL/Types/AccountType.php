<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Account;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class AccountType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Account',
        'description' => 'An account as defined by Riot API',
        'model' => Account::class,
    ];

    public function fields(): array
    {
        return [
            'puuid' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The PUUID of the account',
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Name of the account of the player',
            ],
            'region' => [
                'type' => Type::nonNull(GraphQL::type('RegionEnum')),
                'description' => 'Region of the account of the player',
            ],
            'tag' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Tag (usually preceded by \'#\') of the account of the player',
            ],
            'refreshedAt' => [
                'type' => Type::string(),
                'description' => 'Last time at which the account was refreshed',
            ],
            'summoner' => [
                'type' => GraphQL::type('Summoner'),
                'description' => 'Optional summoner associated with the account',
            ],
        ];
    }
}
