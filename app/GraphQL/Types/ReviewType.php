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
        'description' => 'A review for a game',
        'model' => Review::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The identifier of the review',
            ],
            'content' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Content of the review'
            ],
            'rating' => [
                'type' => Type::nonNull(Type::float()),
                'description' => 'Rating of the player\'s performance'
            ],
            'created_at' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Creation date of the review'
            ],
            'updated_at' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Modification date of the review'
            ],
            'reviewer' => [
                'type' => GraphQL::type('Participant'),
                'description' => 'Reviewer (author) of the review',
            ],
            'reviewee' => [
                'type' => GraphQL::type('Participant'),
                'description' => 'Reviewee (recipient) of the review'
            ],
        ];
    }
}
