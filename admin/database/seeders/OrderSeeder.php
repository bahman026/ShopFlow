<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::all()->each->delete();

        Order::factory()->count(20)->create();
        Order::factory()->count(5)->paid()->create();
        Order::factory()->count(3)->forPartner()->create();
    }
}
