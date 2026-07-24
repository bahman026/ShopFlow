<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Variety;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()->truncate();

        Product::factory()
            ->count(20)
            ->withImages()
            ->has(
                Variety::factory()
                    ->count(3)
                    ->published()
                    ->inStock()
                    ->withImage()
            )
            ->create();
    }
}
