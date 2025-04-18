<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\LoLMatch;
use App\Models\Participant;
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

        Account::factory()
            ->has(
                Summoner::factory()
            )
            ->count(10)
            ->create();

        Account::factory()
            ->count(1)
            ->create();
    }
}
