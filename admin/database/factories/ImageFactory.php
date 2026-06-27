<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
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
            'path' => self::placeholderUrl(),
            'imageable_id' => $imageableType::factory(),
            'imageable_type' => $imageableType,
        ];
    }

    /**
     * A live placeholder image URL.
     *
     * Faker's default `imageUrl()` points at via.placeholder.com, which has
     * been shut down; placehold.co is a working replacement.
     */
    public static function placeholderUrl(int $width = 640, int $height = 480): string
    {
        $color = ltrim(fake()->hexColor(), '#');

        return "https://placehold.co/{$width}x{$height}/{$color}/ffffff?text=" . urlencode(fake()->word());
    }
}
