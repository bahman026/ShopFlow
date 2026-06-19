<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DiscountForEnum;
use App\Models\Discount;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discount>
 */
class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'variety_id' => Variety::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'priority' => fake()->numberBetween(0, 10),
            'is_percent' => fake()->boolean(),
            'amount' => fake()->numberBetween(1, 90),
            'started_at' => null,
            'ended_at' => null,
            'sold' => 0,
            'max_sell' => fake()->numberBetween(10, 100),
            'max_sell_by_user' => fake()->numberBetween(1, 10),
            'is_for' => fake()->randomElement(DiscountForEnum::cases()),
        ];
    }
}
