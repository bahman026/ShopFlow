<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShippingCity;
use App\Models\ShippingLine;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingLineSeeder extends Seeder
{
    public function run(): void
    {
        // shipping_methods (and, through it, shipping_cities) reference
        // shipping_lines, so re-seeding must clear those dependents first or
        // deleting an existing line throws a foreign key violation.
        ShippingCity::query()->delete();
        ShippingMethod::query()->delete();
        ShippingLine::query()->delete();

        ShippingLine::factory()->count(20)->create();
    }
}
