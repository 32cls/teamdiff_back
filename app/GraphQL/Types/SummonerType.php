<?php

namespace App\GraphQL\Types;
use App\Models\Summoner;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class SummonerType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Summoner',
        'description' => 'A summoner as defined by Riot API',
        'model' => Summoner::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'Identifier of the summoner',
            ],
            'icon' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Icon id of the summoner',
            ],
            'level' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Experience level of the summoner',
            ],
            'participants' => [
                'type' => Type::listOf(GraphQL::type('Participant')),
                'description' => 'History of participation of the summoner',
            ],
        ];
    }
}
