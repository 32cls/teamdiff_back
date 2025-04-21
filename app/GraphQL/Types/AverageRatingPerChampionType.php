<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class AverageRatingPerChampionType extends GraphQLType
{
    protected $attributes = [
        'name' => 'AverageRatingPerChampion',
        'description' => 'Champion-specific average rating on reviews',
    ];

    public function fields(): array
    {
        return [
            'championId' => [
                'type' => Type::int(),
                'description' => 'Champion ID',
            ],
            'averageRating' => [
                'type' => Type::float(),
                'description' => 'Average rating for this champion',
            ],
        ];
    }
}
