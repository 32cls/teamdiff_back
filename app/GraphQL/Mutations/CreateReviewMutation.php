<?php

namespace App\GraphQL\Mutations;

use App\Models\LoLMatch;
use App\Models\Review;
use App\Models\Summoner;
use GraphQL\Error\Error;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\Type;

class CreateReviewMutation extends Mutation
{

    protected $attributes = [
        'name' => 'create_review',
    ];

    public function type(): Type
    {
        return GraphQL::type('ReviewSeeder');
    }

    public function args(): array
    {
        return [
            'content' => [
                'name' => 'content',
                'type' => Type::nonNull(Type::string()),
                'description' => 'Content of the review',
                'rules' => ['required'],
            ],
            'rating' => [
                'name' => 'rating',
                'type' => Type::nonNull(Type::float()),
                'description' => 'The rating of the review',
                'rules' => ['required'],
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

    /**
     * @throws Error
     */
    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $hardcoded_reviewer_id = "lTP48_kb1TjEwD00tYyPKMMM7RuK6gnIVo2M3dfxSL9ENYTG";

        if ($hardcoded_reviewer_id != $args['reviewee_id'])
        {
            $reviewer = Summoner::where('id', $hardcoded_reviewer_id)->first();
            $reviewee = Summoner::where('id', $args['reviewee_id'])->first();
            $match = LoLMatch::where('id', $args['match_id'])->first();
            if (!isset($reviewer, $reviewee, $match)){
                throw new Error("Bad request, invalid data supplied");
            }
            if (Review::where(['reviewer_id' => $hardcoded_reviewer_id , 'reviewee_id' => $reviewee->id, 'match_id' => $match->id])->exists())
            {
                throw new Error("A review already exists for this match with provided reviewer/reviewee tuple");
            }
            $review = Review::make([
                'content' => $args['content'],
                'rating' => $args['rating'],
            ]);
            $review->reviewer()->associate($reviewer);
            $review->reviewee()->associate($reviewee);
            $review->match()->associate($match);
            $review->save();
            return $review;
        }
        else
        {
            throw new Error('Bad request, summoner can\'t review their own performance');
        }

    }

}
