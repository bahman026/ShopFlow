<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cart;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        Cart::all()->each->delete();

        Cart::factory()->count(20)->create();
        Cart::factory()->count(5)->guest()->create();
    }
}
