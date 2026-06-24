<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrderShipping;
use Illuminate\Database\Seeder;

class OrderShippingSeeder extends Seeder
{
    public function run(): void
    {
        OrderShipping::all()->each->delete();

        OrderShipping::factory()->count(20)->create();
    }
}
