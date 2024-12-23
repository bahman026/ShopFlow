<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ancestor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeGroup>
 */
class AttributeGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ancestor_id' => Ancestor::factory(),
            'name' => $this->faker->word(),
            'label' => $this->faker->word(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
