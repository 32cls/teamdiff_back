<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ReviewPostRequest;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

class ReviewController
{
    public function indexForUserAsAuthor(User $user): ResourceCollection
    {
        $reviewQuery = $user->summoner->authoredReviews()->with(
            'author.summoner.user',
            'subject.summoner.user',
        );

        return QueryBuilder::for($reviewQuery)
            ->allowedFilters(AllowedFilter::operator('rating', FilterOperator::DYNAMIC))
            ->allowedSorts('created_at', 'updated_at')
            ->simplePaginate()
            ->withQueryString();
    }

    public function indexForUserAsSubject(User $user): ResourceCollection
    {
        $reviewQuery = $user->summoner->subjectedToReviews()->with(
            'author.summoner.user',
            'subject.summoner.user',
        );

        return QueryBuilder::for($reviewQuery)
            ->allowedFilters(AllowedFilter::operator('rating', FilterOperator::DYNAMIC))
            ->allowedSorts('created_at', 'updated_at')
            ->simplePaginate()
            ->withQueryString()
            ->toResourceCollection();
    }

    public function storeForUser(ReviewPostRequest $request, User $user)
    {
        $review = Review::make($request->validated());
        $review->author_player_id = auth()->id();
        $review->subject_player_id = $user->id;

        return $review->save()->toResource();
    }
}
