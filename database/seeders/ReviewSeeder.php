<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Participation;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $randomparticipations = Participation::inRandomOrder()->take(10)->get();
        Review::factory()->recycle($randomparticipations)->count(20)->create();
    }
}
