<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VarietyStatusEnum;
use App\Models\Product;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Factories\Factory;

class VarietyFactory extends Factory
{
    protected $model = Variety::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'attribute_value' => $this->faker->word(),
            'color' => $this->faker->safeColorName(),
            'price' => $this->faker->numberBetween(1000, 100000),
            'sale_price' => $this->faker->optional()->numberBetween(500, 90000),
            'inventory' => $this->faker->numberBetween(0, 100),
            'has_stock' => $this->faker->boolean(80), // 80% chance of being true
            'status' => fake()->randomElement(VarietyStatusEnum::cases()),
        ];
    }
}
