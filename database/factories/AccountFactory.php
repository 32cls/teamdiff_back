<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{

    public function definition(): array
    {
        return [
            'puuid' => fake()->uuid(),
            'name' => fake()->unique()->name(),
            'tag' => fake()->text(5),
            'refreshed_at' => fake()->dateTime('now')->format('Y-m-d H:i:s'),
        ];
    }
}
