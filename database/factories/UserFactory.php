<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'riot_puuid' => Str::random(78),
            'name' => fake()->unique()->userName(),
            'tag' => Str::random(fake()->numberBetween(3, 5)),
            'refreshed_at' => now(),
        ];
    }
}
