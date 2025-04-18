<?php

namespace App\GraphQL\Types;

use App\Models\Participant;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use Rebing\GraphQL\Support\Facades\GraphQL;
class ParticipantType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Participant',
        'description' => 'The summoner that played in the match',
        'model' => Participant::class,
    ];

    public function fields(): array
    {
        return [
            'champion_id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Identifier of the champion played by the summoner"',
            ],
            'team_id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Team ID of the summoner',
            ],
            'win' => [
                'type' => Type::nonNull(Type::boolean()),
                'description' => 'Whether the summoner won the match',
            ],
            'kills' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Number of kills made by the summoner in the match',
            ],
            'deaths' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Number of deaths made by the summoner in the match',
            ],
            'assists' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Number of assists made by the summoner in the match',
            ],
            'level' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Level of the champion played by the summoner',
            ],
            'reviews' => [
                'type' => Type::listOf(GraphQL::type('Review')),
                'description' => 'List of reviews of the summoner in the match',
            ],
            'lolmatch' => [
                'type' => Type::nonNull(GraphQL::type('LoLMatch')),
                'description' => 'Related match linked to player participation',
            ]
        ];
    }

}
