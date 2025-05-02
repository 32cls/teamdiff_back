<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Player;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => fake()->text(fake()->numberBetween(20, 300)),
            'rating' => fake()->numberBetween(1, 5),
        ];
    }

    public function withAuthorAndSubject(Player $author, Player $subject): Factory
    {
        return $this->state(function (array $attributes) use ($author, $subject) {
            return [
                'author_id' => $author->id,
                'subject_id' => $subject->id,
            ];
        });
    }
}
