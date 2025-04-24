<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ReviewSummaryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ReviewSummary',
        'description' => 'Aggregated rating of reviews',
    ];

    public function fields(): array
    {
        return [
            'totalRatingCount' => [
                'type' => Type::int(),
                'description' => 'The total number of reviews for this summoner',
            ],
            'totalAverageRating' => [
                'type' => Type::float(),
                'description' => 'Total average rating for this summoner',
            ],
            'averageRatingPerChampion' => [
                'type' => Type::listOf(GraphQL::type('AverageRatingPerChampion')),
                'description' => 'Average rating broken down by champion',
            ],
        ];
    }
}
