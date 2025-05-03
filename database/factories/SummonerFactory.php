<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RegionEnum;
use App\Models\Summoner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Summoner>
 */
class SummonerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'riot_summoner_id' => Str::random(25),
            'region' => fake()->randomElement(RegionEnum::valueArray()),
            'icon_id' => Str::random(5),
            'level' => fake()->numberBetween(1, 1000),
        ];
    }
}
