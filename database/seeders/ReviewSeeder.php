<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Player::get()->random(DatabaseSeeder::$gameCount * 8) as $player) {
            $mates = $player->game->players->where('id', '!=', $player->id);

            foreach ($mates->random(6) as $mate) {
                Review::factory()
                    ->for($player, 'author')
                    ->for($mate, 'subject')
                    ->createOne();
            }
        }
    }
}
