<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Summoner>
 */
class SummonerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => fake()->numberBetween(0, 200),
            'revision_date' => fake()->dateTime('now')->format('Y-m-d H:i:s'),
            'level' => fake()->numberBetween(1, 1000),
        ];
    }
}
