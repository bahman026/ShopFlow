<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderShippingPaymentTypeEnum;
use App\Models\Order;
use App\Models\OrderShipping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderShipping>
 */
class OrderShippingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'checker_id' => null,
            'pack_count' => fake()->numberBetween(1, 4),
            'pack_user' => null,
            'checked_at' => null,
            'shipped_at' => null,
            'sender_id' => null,
            'courier_id' => null,
            'courier_received_at' => null,
            'courier_delivered_at' => null,
            'confirm_code' => fake()->optional()->numerify('######'),
            'cheque_is_require' => false,
            'courier2_id' => null,
            'description' => fake()->optional()->sentence(),
            'payment_type' => fake()->randomElement(OrderShippingPaymentTypeEnum::cases()),
        ];
    }
}
