<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Participation;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ParticipationType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Participation',
        'description' => 'The summoner that played in the match',
        'model' => Participation::class,
    ];

    public function fields(): array
    {
        return [
            'summoner' => [
                'type' => Type::nonNull(GraphQL::type('Summoner')),
                'description' => 'The summoner associated with the participation',
            ],
            'championId' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Identifier of the champion played by the summoner"',
            ],
            'teamId' => [
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
            'role' => [
                'type' => Type::nonNull(GraphQL::type('RoleEnum')),
                'description' => 'Role of the summoner',
            ],
            'assists' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Number of assists made by the summoner in the match',
            ],
            'level' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Level of the champion played by the summoner',
            ],
            'writtenReviews' => [
                'type' => Type::listOf(GraphQL::type('Review')),
                'description' => 'List of reviews written by the summoner in the match',
            ],
            'receivedReviews' => [
                'type' => Type::listOf(GraphQL::type('Review')),
                'description' => 'List of reviews received by the summoner in the match',
            ],
            'lolMatch' => [
                'type' => Type::nonNull(GraphQL::type('LolMatch')),
                'description' => 'Related match linked to player participation',
            ],
        ];
    }
}
