<?php

namespace Database\Factories;


use App\Models\Summoner;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{

    public function definition()
    {
        return [
            "summoner_id" => Summoner::factory(),
            'champion_id' => fake()->numberBetween(0,200),
            'team_id' => fake()->numberBetween(0,1),
            'team_position' => 'BOTTOM',
            'win' => fake()->boolean(),
            'kills' => fake()->numberBetween(0,40),
            'deaths' => fake()->numberBetween(0,40),
            'assists' => fake()->numberBetween(0,40),
            'level' => fake()->numberBetween(1,18)
        ];
    }
}
