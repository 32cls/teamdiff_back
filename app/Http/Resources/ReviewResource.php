<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Review $review */
        $review = $this->resource;

        $review->load('author', 'subject');

        return $review->only([
            'id',
            'author',
            'subject',
            'is_from_ally',
            'content',
            'rating',
            'created_at',
            'updated_at',
            ...$review->trashed() ? ['deleted_at'] : [],
        ]);
    }
}
