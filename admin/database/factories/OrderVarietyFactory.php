<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderVariety>
 */
class OrderVarietyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->numberBetween(100_000, 3_000_000);
        $discount = fake()->randomElement([0, 0, fake()->numberBetween(10_000, 100_000)]);

        return [
            'order_id' => Order::factory(),
            'variety_id' => Variety::factory(),
            'product_id' => fn (array $attributes): int => Variety::findOrFail($attributes['variety_id'])->product_id,
            'sub_order_id' => null,
            'quantity' => $quantity,
            'gather_quantity' => 0,
            'invoice_quantity' => 0,
            'price' => $price,
            'discount' => $discount,
            'coupon_discount' => 0,
            'final_price' => $price * $quantity - $discount,
        ];
    }
}
