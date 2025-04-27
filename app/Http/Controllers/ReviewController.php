<?php

namespace App\Http\Controllers;

use App\Models\LoLMatch;
use App\Models\Participation;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ReviewController extends Controller
{

    public function destroy(Review $review)
    {
        $review->delete();
        return response()->noContent();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'content' => 'required|max:1000',
            'rating' => 'required|numeric|min:1|max:5',
            'matchId' => 'required',
            'receiverId' => 'required',
        ]);
        $hardcodedReviewerId = "lTP48_kb1TjEwD00tYyPKMMM7RuK6gnIVo2M3dfxSL9ENYTG";

        if ($hardcodedReviewerId == $request->receiverId)
        {
            throw new BadRequestException('Bad request, summoner can\'t review their own performance');
        }
        else
        {
            $match = LoLMatch::where('id', $request->matchId)->first();
            $reviewer = Participation::where('summonerId', $hardcodedReviewerId)
                ->where('matchId', $request->matchId)
                ->first();
            $receiver = Participation::where('summonerId', $request->receiverId)
                ->where('matchId', $request->matchId)
                ->first();
            if(!$match || !$reviewer || !$receiver){
                throw new BadRequestException("Bad request");
            }
            $exists = Review::where('reviews.reviewerId', $reviewer->id)
                ->where('reviews.receiverId', $receiver->id)
                ->join('participations', 'participations.id', '=', 'reviews.receiverId')
                ->where('participations.matchId', $match->id)
                ->exists();
            if($exists)
            {
                throw new BadRequestException("A review already exists for this match with provided reviewer/receiver tuple");
            }
            $review = Review::make([
                'content' => $request['content'],
                'rating' => $request->rating,
                'isAlly' => $reviewer->teamId == $receiver->teamId
            ]);
            $review->reviewer()->associate($reviewer);
            $review->receiver()->associate($receiver);
            $review->save();
            return $review->json();
        }
    }
}
