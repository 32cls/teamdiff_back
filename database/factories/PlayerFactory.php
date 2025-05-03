<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Enums\TeamEnum;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'riot_champion_name' => fake()->userName(),
            'riot_team' => fake()->randomElement(TeamEnum::valueArray()),
            'riot_role' => fake()->randomElement(RoleEnum::valueArray()),
            'kills' => fake()->numberBetween(0, 30),
            'deaths' => fake()->numberBetween(0, 30),
            'assists' => fake()->numberBetween(0, 30),
            'level' => fake()->numberBetween(3, 18),
        ];
    }
}
