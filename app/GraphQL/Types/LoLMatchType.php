<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use App\Models\LoLMatch;
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
            'game_creation' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Datetime (UTC) at which the match was created',
            ],
            'participants' => [
                'type' => Type::listOf(GraphQL::type('Participant')),
                'description' => 'List of participants in the match',
            ],
        ];
    }

}
