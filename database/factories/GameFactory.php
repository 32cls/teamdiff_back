<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Player;
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
        ];
    }
}
