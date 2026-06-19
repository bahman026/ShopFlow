<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ancestor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ancestor>
 */
class AncestorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(), // Random word for the name
            'order' => $this->faker->numberBetween(1, 100), // Random number between 1 and 100 for sorting
        ];
    }
}
