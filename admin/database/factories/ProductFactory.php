<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductStatusEnum;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {

        return [
            'heading' => fake()->text,
            'slug' => fn (array $attributes): string => Str::slug($attributes['heading']),
            'price' => fake()->numberBetween(1000, 100000),
            'content' => fake()->paragraph(),
            'title' => fake()->words(3, true),
            'description' => fake()->text(200),
            'no_index' => fake()->boolean,
            'canonical' => null,
            'attribute_group_id' => AttributeGroup::factory(),
            'category_id' => Category::query()->inRandomOrder()->first()?->id ?? Category::factory(),
            'brand_id' => Brand::factory(),
            'minimum' => 1,
            'maximum' => null,
            'step' => 1,
            'profit_percent' => fake()->numberBetween(0, 50),
            'attributes' => null,
            'highlight' => null,
            'has_stock' => fake()->boolean,
            'variety_counts' => fake()->numberBetween(0, 5),
            'weight' => fake()->randomNumber(3),
            'length' => fake()->randomNumber(3),
            'width' => fake()->randomNumber(3),
            'height' => fake()->randomNumber(3),
            'status' => fake()->randomElement(ProductStatusEnum::cases()),
            'seen' => fake()->numberBetween(0, 1000),
        ];
    }

    public function withImages(int $count = 3): static
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $product->images()->create([
                    'path' => fake()->imageUrl(),
                    'is_featured' => $i === 0, // First image featured
                    'order' => $i,
                    'alt_text' => fake()->words(2, true),
                    'imageable_type' => Product::class,
                    'imageable_id' => $product->id,
                ]);
            }
        });
    }
}
