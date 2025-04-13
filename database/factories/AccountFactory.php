<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{

    public function definition(): array
    {
        return [
            'puuid' => fake()->uuid(),
            'name' => fake()->unique()->userName(),
            'tag' => fake()->text(5),
            'refreshed_at' => fake()->dateTimeThisMonth('now')->format('Y-m-d H:i:s'),
        ];
    }
}
