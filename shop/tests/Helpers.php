<?php

declare(strict_types=1);

use App\Enums\CategoryStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Variety;

/**
 * Shared catalog test helpers used across catalog feature tests
 * (category, brand, ...). Loaded via tests/Pest.php.
 */
if (! function_exists('catImage')) {
    function catImage(string $type, int $id): void
    {
        Image::create([
            'path' => 'media/sample.jpg',
            'imageable_type' => $type,
            'imageable_id' => $id,
            'alt_text' => 'تصویر نمونه',
            'is_featured' => true,
        ]);
    }
}

if (! function_exists('catCategory')) {
    function catCategory(string $slug = 'shoes', ?int $parentId = null): Category
    {
        return Category::create([
            'heading' => 'دسته '.$slug,
            'slug' => $slug,
            'status' => CategoryStatusEnum::ACTIVE,
            'parent_id' => $parentId,
        ]);
    }
}

if (! function_exists('catProduct')) {
    function catProduct(Category $category, int $price = 100000, ?int $brandId = null, ?int $attributeId = null, bool $hasStock = true): Product
    {
        $product = Product::create([
            'heading' => 'محصول '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'price' => $price,
            'category_id' => $category->id,
            'brand_id' => $brandId,
            'has_stock' => $hasStock,
            'status' => ProductStatusEnum::PUBLISHED,
            'seen' => 0,
        ]);
        catImage(Product::class, $product->id);

        Variety::create([
            'product_id' => $product->id,
            'price' => $price,
            'inventory' => 5,
            'has_stock' => true,
            'status' => VarietyStatusEnum::PUBLISHED,
        ]);

        // product_attribute is the documented filters-to-products link.
        if ($attributeId !== null) {
            $product->attributes()->attach($attributeId, ['is_highlight' => false]);
        }

        return $product;
    }
}
