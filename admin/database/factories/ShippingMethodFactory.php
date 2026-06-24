<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ShippingMethodForEnum;
use App\Models\ShippingLine;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_line_id' => ShippingLine::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->optional()->randomElement(['Express', 'Special', 'Economy', 'Standard']),
            'min_count' => fake()->optional()->numberBetween(1, 10),
            'min_amount' => fake()->optional()->numberBetween(100000, 1000000),
            'for' => fake()->randomElement(ShippingMethodForEnum::cases()),
            'disable_from' => null,
            'disable_to' => null,
            'status' => true,
        ];
    }
}
