<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShippingLine;
use Illuminate\Database\Seeder;

class ShippingLineSeeder extends Seeder
{
    public function run(): void
    {
        ShippingLine::all()->each->delete();

        ShippingLine::factory()->count(20)->create();
    }
}
