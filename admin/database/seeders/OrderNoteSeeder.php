<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrderNote;
use Illuminate\Database\Seeder;

class OrderNoteSeeder extends Seeder
{
    public function run(): void
    {
        OrderNote::all()->each->delete();

        OrderNote::factory()->count(20)->create();
    }
}
