<?php

namespace App\GraphQL\Inputs;

use Rebing\GraphQL\Support\InputType;
use GraphQL\Type\Definition\Type;
class ReviewInput extends InputType
{
    protected $attributes = [
        'name' => 'ReviewInput',
        'description' => 'Content and mandatory information for a review',
    ];

    public function fields(): array
    {
        return [
            'content' => [
                'name' => 'content',
                'type' => Type::nonNull(Type::string()),
                'description' => 'The content of the review',
                'rules' => ['required', 'max:1000'],
            ],
            'rating' => [
                'name' => 'rating',
                'type' => Type::nonNull(Type::float()),
                'description' => 'The rating of the review',
                'rules' => ['required', 'numeric', 'min:0', 'max:5'],
            ],
            'match_id' => [
                'name' => 'match_id',
                'type' => Type::nonNull(Type::string()),
                'description' => 'The identifier of the match',
                'rules' => ['required'],
            ],
            'reviewee_id' => [
                'name' => 'reviewee_id',
                'type' => Type::nonNull(Type::string()),
                'description' => 'The identifier of the reviewee',
                'rules' => ['required'],
            ]
        ];
    }

}
