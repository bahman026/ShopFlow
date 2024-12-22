<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BrandStatusEnum;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'heading' => fake()->text,
            'slug' => fn (array $attributes): string => Str::slug($attributes['heading']),
            'content' => fake()->optional()->paragraph(),
            'title' => fake()->optional()->text,
            'description' => fake()->optional()->text,
            'no_index' => fake()->boolean,
            'status' => fake()->randomElement(BrandStatusEnum::cases()),
        ];
    }

    public function withImage(): BrandFactory | Factory
    {
        return $this->afterCreating(function (Brand $brand) {
            $brand->image()->create([
                'path' => fake()->imageUrl(),
                'imageable_type' => Brand::class,
                'imageable_id' => $brand->id,
            ]);
        });
    }
}
