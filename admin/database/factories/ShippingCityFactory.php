<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\ShippingCity;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingCity>
 */
class ShippingCityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_method_id' => ShippingMethod::factory(),
            'province_id' => null,
            'city_id' => City::inRandomOrder()->first()?->id,
            'pay_on_delivery' => fake()->boolean(20),
            'amount' => fake()->optional(0.8)->numberBetween(0, 200000),
            'sending_days' => fake()->optional()->randomElement(['1,2,3,4,5', '6,7', '1,3,5']),
            'disable_from' => null,
            'disable_to' => null,
            'delay' => fake()->optional()->numberBetween(0, 5),
            'description' => fake()->optional()->sentence(),
            'status' => true,
        ];
    }
}
