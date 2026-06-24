<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShippingLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingLine>
 */
class ShippingLineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'cost' => fake()->numberBetween(0, 500000),
        ];
    }
}
