<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\Game;
use App\Models\Player;
use App\Models\Summoner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'riot_summoner_id' => Summoner::factory()->definition()['riot_summoner_id'],
            'champion_internal_name' => fake()->userName(),
            'team_id' => fake()->numberBetween(1, 2),
            'role' => fake()->randomElement(RoleEnum::valueArray()),
            'has_won' => fake()->boolean(),
            'kills' => fake()->numberBetween(0, 30),
            'deaths' => fake()->numberBetween(0, 30),
            'assists' => fake()->numberBetween(0, 30),
            'level' => fake()->numberBetween(3, 18),
        ];
    }

    public function withGame(Game $game): Factory
    {
        return $this->state(function (array $attributes) use ($game) {
            return [
                'riot_match_id' => $game->riot_match_id,
            ];
        });
    }
}
