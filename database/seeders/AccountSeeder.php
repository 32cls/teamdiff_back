<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Summoner;
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
