<?php

declare(strict_types=1);

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
            'count' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Number of reviews for the champion',
            ],
            'championId' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Identifier of the champion',
            ],
            'rating' => [
                'type' => Type::nonNull(Type::float()),
                'description' => 'Average rating for this champion',
            ],
        ];
    }
}
