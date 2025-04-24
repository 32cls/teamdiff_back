<?php

namespace Database\Seeders;

use App\Models\LoLMatch;
use App\Models\Participation;
use App\Models\Summoner;
use Illuminate\Database\Seeder;

class SummonerSeeder extends Seeder
{
    public function run(): void
    {
        $summoners = Summoner::take(10)->get();

        LoLMatch::factory()
            ->count(10) // create 10 matches
            ->create()
            ->each(function ($match) use ($summoners) {

                // Shuffle to avoid duplicates and take a unique set
                $uniqueSummoners = $summoners->shuffle()->take(10);

                foreach ($uniqueSummoners as $summoner) {
                    Participation::factory()
                        ->recycle($summoner) // reuse the same summoner instance
                        ->create([
                            'matchId' => $match->id,
                            'summonerId' => $summoner->id,
                        ]);
                }
            });
    }
}
