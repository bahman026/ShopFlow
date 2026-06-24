<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Receipt;
use Illuminate\Database\Seeder;

class ReceiptSeeder extends Seeder
{
    public function run(): void
    {
        Receipt::all()->each->delete();

        Receipt::factory()->count(20)->create();
    }
}
