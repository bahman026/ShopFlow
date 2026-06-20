<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'heading' => fake()->sentence(6),
            'content' => fake()->paragraph(),
            'order' => fake()->numberBetween(0, 100),
            'position' => fake()->optional()->randomElement(['homepage', 'products']),
        ];
    }
}
