<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Participation;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'content' => fake()->paragraph(),
            'rating' => fake()->randomFloat(1, 0, 5),
            'receiverId' => Participation::factory(),
            'reviewerId' => Participation::factory(),
            'isAlly' => fake()->boolean(),
        ];
    }
}
