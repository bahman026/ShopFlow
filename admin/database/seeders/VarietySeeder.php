<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Variety;
use Illuminate\Database\Seeder;

class VarietySeeder extends Seeder
{
    public function run(): void
    {
        Variety::query()->truncate();
        Variety::factory()->count(20)->withImage()->create();
    }
}
