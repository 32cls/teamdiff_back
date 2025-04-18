<?php

namespace App\GraphQL\Mutations;

use App\Models\LoLMatch;
use App\Models\Participant;
use App\Models\Review;
use App\Models\Summoner;
use Closure;
use GraphQL\Error\Error;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Log;
use Rebing\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\Type;

class CreateReviewMutation extends Mutation
{

    protected $attributes = [
        'name' => 'create_review',
    ];

    public function type(): Type
    {
        return GraphQL::type('Review');
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => GraphQL::type('ReviewInput'),
            ]
        ];
    }

    /**
     * @throws Error
     */
    public function resolve($root, $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        /** @var SelectFields $fields */
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $hardcoded_reviewer_id = "lTP48_kb1TjEwD00tYyPKMMM7RuK6gnIVo2M3dfxSL9ENYTG";
        $input = $args['input'];
        if ($hardcoded_reviewer_id == $input['reviewee_id'])
        {
            throw new Error('Bad request, summoner can\'t review their own performance');
        }
        else
        {
            $match = LoLMatch::where('id', $input['match_id'])->first();
            $reviewer = Participant::where('summoner_id', $hardcoded_reviewer_id)->first();
            $reviewee = Participant::where('summoner_id', $input['reviewee_id'])->first();
            if(!$match || !$reviewer || !$reviewee){
                throw new Error("Bad request");
            }
            if (!$match->participants()->whereIn('summoner_id', [$hardcoded_reviewer_id, $input['reviewee_id']])->exists())
            {
                throw new Error("Bad request, the match does not contain the participants");
            }
            if (Review::where(['reviewer_id' => $reviewer->id, 'reviewee_id' => $reviewee->id])->exists())
            {
                throw new Error("A review already exists for this match with provided reviewer/reviewee tuple");
            }
            $review = Review::make([
                'content' => $input['content'],
                'rating' => $input['rating'],
            ]);
            $review->reviewer()->associate($reviewer);
            $review->reviewee()->associate($reviewee);
            $review->save();
            return Review::select($select)->with($with)->find($review->id);
        }

    }

}
