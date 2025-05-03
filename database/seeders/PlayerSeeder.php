<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\TeamEnum;
use App\Models\Game;
use App\Models\Player;
use App\Models\Summoner;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Game::all() as $game) {
            $roleWinLoseSequence = collect(RoleEnum::valueArray())
                ->shuffle()
                ->crossJoin([TeamEnum::Blue, TeamEnum::Red])
                ->map(fn (array $items) => [
                    'riot_role' => $items[0],
                    'riot_team_id' => $items[1],
                ]);

            $summonerSequence = Summoner::all()
                ->shuffle()
                ->map(fn (Summoner $s) => ['summoner_id' => $s->id]);

            Player::factory()
                ->count(10)
                ->sequence(...$roleWinLoseSequence)
                ->sequence(...$summonerSequence)
                ->for($game)
                ->create();
        }
    }
}
