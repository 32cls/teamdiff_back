<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Game;
use App\Models\Player;
use App\Models\Summoner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleWinLoseSequence = collect(RoleEnum::valueArray())
            ->crossJoin([true, false])
            ->map(fn(array $items) => [
                'role' => $items[0],
                'has_won' => $items[1],
                'team_id' => $items[1] + 1,
            ]);

        $summonerSequence = Summoner::all()
            ->map(fn(Summoner $s) => ['riot_summoner_id'=>$s->riot_summoner_id]);

        foreach (Game::all() as $i => $game) {
            Player::factory()
                ->count(10)
                ->sequence(...$roleWinLoseSequence)
                ->sequence(...$summonerSequence)
                ->withGame($game)
                ->create();
        }
    }
}
