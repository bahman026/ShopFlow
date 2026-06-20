<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VarietyStatusEnum;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Variety>
 */
class VarietyFactory extends Factory
{
    protected $model = Variety::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'attribute_id' => null,
            'attribute_value' => fake()->word(),
            'color' => fake()->safeColorName(),
            'price' => fake()->numberBetween(1000, 100000),
            'sale_price' => fake()->optional()->numberBetween(500, 90000),
            'inventory' => fake()->numberBetween(0, 100),
            'has_stock' => fake()->boolean(80),
            'status' => fake()->randomElement(VarietyStatusEnum::cases()),
        ];
    }

    public function withAttribute(Attribute $attribute): static
    {
        return $this->state([
            'attribute_id' => $attribute->id,
        ]);
    }
}
