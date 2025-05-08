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
        return QueryBuilder::for($user->summoner->authoredReviews())
            ->allowedFilters([
                AllowedFilter::operator('rating', FilterOperator::DYNAMIC),
            ])
            ->paginate()
            ->withQueryString()
            ->toResourceCollection();
    }

    public function indexForUserAsSubject(User $user): ResourceCollection
    {
        return QueryBuilder::for($user->summoner->subjectedToReviews())
            ->allowedFilters([
                AllowedFilter::operator('rating', FilterOperator::DYNAMIC),
            ])
            ->paginate()
            ->withQueryString()
            ->toResourceCollection();
    }

    public function storeForUser(ReviewPostRequest $request, User $user)
    {
        $review = Review::make($request->validated());
        $review->author()->associate(auth()->id());
        $review->subject()->associate($user);

        return $review->save()->toResource();
    }
}
