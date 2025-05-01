<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Game;
use App\Models\Player;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $game1 = Game::factory()->create();

        $rolesWinLoseSequence = collect(RoleEnum::valueArray())
            ->crossJoin([true, false])
            ->map(fn(array $items) => ['role' => $items[0], 'has_won' => $items[1]]);

        Player::factory()
            ->count(10)
            ->sequence(...$rolesWinLoseSequence)
            ->withGame($game1)
            ->create();
    }
}
