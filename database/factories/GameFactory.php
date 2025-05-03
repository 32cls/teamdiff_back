<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TeamEnum;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'riot_match_id' => 'EUW1_'.Str::random(10),
            'duration' => fake()->numberBetween(180, 3600),
            'started_at' => fake()->dateTimeBetween('-10 days'),
            'winning_team' => fake()->randomElement(TeamEnum::valueArray()),
        ];
    }
}
