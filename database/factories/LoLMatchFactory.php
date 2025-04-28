<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoLMatch>
 */
class LoLMatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->text('10'),
            'duration' => fake()->numberBetween(0, 3600),
            'gameCreation' => fake()->dateTimeThisMonth('now')->format('Y-m-d H:i:s'),
        ];
    }
}
