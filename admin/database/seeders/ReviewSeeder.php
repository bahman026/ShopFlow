<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        Review::all()->each->delete();

        Review::factory()->count(20)->create();
    }
}
