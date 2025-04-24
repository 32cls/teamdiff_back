<?php

namespace App\GraphQL\Types;

use App\Models\Review;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use Rebing\GraphQL\Support\Facades\GraphQL;


class ReviewType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Review',
        'description' => 'A review for a match',
        'model' => Review::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The unique identifier of the review',
            ],
            'content' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Content of the review'
            ],
            'rating' => [
                'type' => Type::nonNull(Type::float()),
                'description' => 'Rating of the player\'s performance'
            ],
            'createdAt' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Creation date of the review'
            ],
            'updatedAt' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Modification date of the review'
            ],
            'reviewer' => [
                'type' => GraphQL::type('Participation'),
                'description' => 'Reviewer (author) of the review',
            ],
            'receiver' => [
                'type' => GraphQL::type('Participation'),
                'description' => 'Receiver of the review'
            ],
            'isAlly' => [
                'type' => Type::nonNull(Type::boolean()),
                'description' => 'Is the summoner reviewed an ally of the reviewer'
            ]
        ];
    }
}
