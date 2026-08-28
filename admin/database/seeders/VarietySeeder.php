<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Variety;
use App\Support\ProductCache;
use Illuminate\Database\Seeder;

class VarietySeeder extends Seeder
{
    public function run(): void
    {
        Variety::query()->truncate();

        $products = Product::query()->get();

        if ($products->isEmpty()) {
            $products = Product::factory()->count(5)->withImages()->create();
        }

        $products->each(function (Product $product): void {
            Variety::factory()
                ->count(fake()->numberBetween(1, 3))
                ->for($product)
                ->published()
                ->inStock()
                ->withImage()
                ->create();
        });

        // `truncate()` fires no model events, so no observer saw the old
        // varieties leave — prices and stock counts would stay cached.
        ProductCache::flushAll();
    }
}
