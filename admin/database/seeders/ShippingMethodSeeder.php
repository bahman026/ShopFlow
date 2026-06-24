<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        ShippingMethod::all()->each->delete();

        ShippingMethod::factory()->count(20)->create();
    }
}
