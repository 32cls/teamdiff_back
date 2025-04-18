<?php

namespace Database\Seeders;

use App\Models\LoLMatch;
use App\Models\Participant;
use App\Models\Summoner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SummonerSeeder extends Seeder
{
    public function run(): void
    {
        $summoners = Summoner::take(10)->get();
        Log::debug($summoners);
        LoLMatch::factory()->has(
            Participant::factory()->recycle($summoners)->count(10)
        )->count(10)->create();
    }
}
