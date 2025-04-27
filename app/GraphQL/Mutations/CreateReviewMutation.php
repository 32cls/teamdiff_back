<?php

namespace App\GraphQL\Mutations;

use App\Http\Traits\RateLimited;
use App\Models\LoLMatch;
use App\Models\Participation;
use App\Models\Review;
use Closure;
use Exception;
use GraphQL;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\SelectFields;

class CreateReviewMutation extends Mutation
{

    use RateLimited;

    protected $attributes = [
        'name' => 'createReview',
    ];

    public function type(): Type
    {
        return GraphQL::type('Review');
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => Type::nonNull(GraphQL::type('ReviewInput')),
            ]
        ];
    }

    /**
     * @throws Exception
     */
    public function resolve($root, $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $this->enforceRateLimit('CreateReviewMutation', 9);
        /** @var SelectFields $fields */
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $hardcodedReviewerId = "lTP48_kb1TjEwD00tYyPKMMM7RuK6gnIVo2M3dfxSL9ENYTG";
        $input = $args['input'];

        if ($hardcodedReviewerId == $input['receiverId'])
        {
            throw new Error('Bad request, summoner can\'t review their own performance');
        }
        else
        {
            $match = LoLMatch::where('id', $input['matchId'])->first();
            $reviewer = Participation::where('summonerId', $hardcodedReviewerId)
                ->where('matchId', $input['matchId'])
                ->first();
            $receiver = Participation::where('summonerId', $input['receiverId'])
                ->where('matchId', $input['matchId'])
                ->first();
            if(!$match || !$reviewer || !$receiver){
                throw new Error("Bad request");
            }
            $exists = Review::where('reviews.reviewerId', $reviewer->id)
                ->where('reviews.receiverId', $receiver->id)
                ->join('participations', 'participations.id', '=', 'reviews.receiverId')
                ->where('participations.matchId', $match->id)
                ->exists();
            if($exists)
            {
                throw new Error("A review already exists for this match with provided reviewer/receiver tuple");
            }
            $review = Review::make([
                'content' => $input['content'],
                'rating' => $input['rating'],
                'isAlly' => $reviewer->teamId == $receiver->teamId
            ]);
            $review->reviewer()->associate($reviewer);
            $review->receiver()->associate($receiver);
            $review->save();
            return Review::select($select)->with($with)->find($review->id);
        }

    }

}
