<?php

namespace App\GraphQL\Mutations;

use App\GraphQL\Traits\RateLimited;
use App\Models\Review;
use Closure;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Ramsey\Uuid\Guid\Guid;
use Rebing\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\Type;

class DeleteReviewMutation extends Mutation
{

    use RateLimited;

    protected $attributes = [
        'name' => 'deleteReview',
    ];

    public function type(): Type
    {
        return Type::nonNull(Type::boolean());
    }

    public function args(): array
    {
        return [
            "id" => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The id of the review to delete',
                'rules' => ['required'],
            ]
        ];
    }

    /**
     * @throws Error
     */
    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields): bool
    {
        $this->enforceRateLimit('DeleteReviewMutation', 20, 10);
        $review = Review::find($args['id']);
        if (!$review) {
            throw new Error('Review not found');
        }
        $review->delete();
        return true;
    }
}
