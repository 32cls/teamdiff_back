<?php

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

        return $review->only([
            'id',
            'author_player_id',
            'subject_player_id',
            'content',
            'rating',
            'created_at',
            'updated_at',
            ...$review->trashed() ? ['deleted_at'] : [],
        ]);
    }
}
