<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Summoner;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipationFactory extends Factory
{
    public function definition()
    {
        return [
            'summonerId' => Summoner::factory(),
            'championId' => fake()->numberBetween(0, 200),
            'teamId' => fake()->numberBetween(0, 1),
            'role' => 'BOTTOM',
            'win' => fake()->boolean(),
            'kills' => fake()->numberBetween(0, 40),
            'deaths' => fake()->numberBetween(0, 40),
            'assists' => fake()->numberBetween(0, 40),
            'level' => fake()->numberBetween(1, 18),
        ];
    }
}
