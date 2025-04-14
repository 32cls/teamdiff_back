<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\LoLMatch;
use App\Models\Review;
use App\Models\Summoner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fake_uuid = fake()->uuid();

        Account::factory()
            ->has(
                Summoner::factory()
                    ->set('id', $fake_uuid)
                    ->count(1)
                    ->hasAttached(
                        LoLMatch::factory()->count(5),
                        [
                            'summoner_id' => $fake_uuid,
                            'champion_id' => 10,
                            'team_id' => 1,
                            'team_position' => 'BOTTOM',
                            'win' => true,
                            'kills' => 1,
                            'deaths' => 6,
                            'assists' => 9,
                            'level' => 18
                        ]
                    )
            )
            ->count(1)
            ->create();

        Account::factory()
            ->count(1)
            ->create();
    }
}
