<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\Review;
use App\Models\Summoner;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $random_participants = Participant::inRandomOrder()->take(10)->get();
        Review::factory()->recycle($random_participants)->count(20)->create();
    }
}
