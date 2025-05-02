<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public static int $userCount = 10;

    public static int $gameCount = 4;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SummonerSeeder::class,
            GameSeeder::class,
            PlayerSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
