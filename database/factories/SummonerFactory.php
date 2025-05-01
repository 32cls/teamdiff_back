<?php

namespace Database\Factories;

use App\Enums\RegionEnum;
use App\Models\Summoner;
use App\Models\User;
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
            'icon_id' => fake()->numberBetween(0, 200),
            'level' => fake()->numberBetween(1, 1000),
        ];
    }

    public function forUser(User $user): Factory
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_puuid' => $user->riot_puuid,
            ];
        });
    }
}
