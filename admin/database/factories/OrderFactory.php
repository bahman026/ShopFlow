<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalProducts = fake()->numberBetween(100_000, 5_000_000);
        $discount = fake()->randomElement([0, 0, 0, fake()->numberBetween(10_000, 200_000)]);
        $shipping = fake()->numberBetween(0, 500_000);
        $tax = (int) round($totalProducts * 0.09);

        return [
            'user_id' => User::factory(),
            'coupon_id' => null,
            'status' => fake()->randomElement(OrderStatusEnum::cases()),
            'coupon_discount' => 0,
            'discount' => $discount,
            'shipping_cost' => $shipping,
            'total_products_price' => $totalProducts,
            'tax' => $tax,
            'total_price' => $totalProducts + $tax + $shipping - $discount,
            'for_partner' => false,
            'content' => fake()->optional()->sentence(),
            'src' => fake()->randomElement(OrderSrcEnum::cases()),
            'version' => null,
        ];
    }

    public function paid(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatusEnum::PAID,
        ]);
    }

    public function forPartner(): self
    {
        return $this->state(fn (array $attributes): array => [
            'for_partner' => true,
        ]);
    }
}
