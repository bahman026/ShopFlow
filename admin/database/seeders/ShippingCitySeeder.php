<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShippingCity;
use Illuminate\Database\Seeder;

class ShippingCitySeeder extends Seeder
{
    public function run(): void
    {
        ShippingCity::all()->each->delete();

        ShippingCity::factory()->count(20)->create();
    }
}
