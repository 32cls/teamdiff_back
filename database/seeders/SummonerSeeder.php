<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Summoner;
use App\Models\User;
use Illuminate\Database\Seeder;

class SummonerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::each(function (User $user) {
            Summoner::factory()
                ->withUser($user)
                ->create();
        });
    }
}
