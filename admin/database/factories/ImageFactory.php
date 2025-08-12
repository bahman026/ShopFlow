<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imageableTypes = [
            Brand::class,
            Category::class,
        ];
        $imageableType = $this->faker->randomElement($imageableTypes);

        return [
            'path' => fake()->imageUrl,
            'imageable_id' => $imageableType::factory(),
            'imageable_type' => $imageableType,
        ];
    }
}
