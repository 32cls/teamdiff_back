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
            'region' => fake()->randomElement(['NA1', 'BR1', 'LA1', 'LA2', 'KR', 'JP1', 'EUN1', 'EUW1', 'ME1', 'TR1', 'RU', 'OC1', 'SG2', 'TW2', 'VN2'])
        ];
    }
}
