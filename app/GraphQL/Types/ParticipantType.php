<?php

namespace App\GraphQL\Types;

use App\Models\Participant;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
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
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'Identifier of the summoner inside the game',
            ],
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
            ]
        ];
    }

}
