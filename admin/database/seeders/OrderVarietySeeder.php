<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrderVariety;
use Illuminate\Database\Seeder;

class OrderVarietySeeder extends Seeder
{
    public function run(): void
    {
        OrderVariety::all()->each->delete();

        OrderVariety::factory()->count(40)->create();
    }
}
