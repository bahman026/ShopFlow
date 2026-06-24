<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => null,
            'variety_id' => Variety::factory(),
            'count' => fake()->numberBetween(1, 5),
        ];
    }

    public function guest(): self
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
            'session_id' => fake()->uuid(),
        ]);
    }
}
