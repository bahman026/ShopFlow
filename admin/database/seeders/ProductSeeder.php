<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Variety;
use App\Support\ProductCache;
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

        // `truncate()` fires no model events, so no observer saw the old rows
        // leave. Without this the storefront keeps serving cached pages for
        // products that no longer exist.
        ProductCache::flushAll();
    }
}
