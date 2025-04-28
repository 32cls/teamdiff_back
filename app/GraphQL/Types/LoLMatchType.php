<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\LoLMatch;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class LoLMatchType extends GraphQLType
{
    protected $attributes = [
        'name' => 'LolMatch',
        'description' => 'A match played by the summoner',
        'model' => LoLMatch::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'Identifier of the match',
            ],
            'duration' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Duration of the match',
            ],
            'gameCreation' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Datetime (UTC) at which the match was created',
            ],
            'participations' => [
                'type' => Type::listOf(GraphQL::type('Participation')),
                'description' => 'List of participations in the match',
            ],
        ];
    }
}
