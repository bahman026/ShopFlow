<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BannerStatusEnum;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => fake()->randomElement(['home-top', 'home-middle', 'category-side']),
            'heading' => fake()->words(3, true),
            'url' => fake()->url(),
            'sort' => fake()->numberBetween(0, 20),
            'status' => fake()->randomElement(BannerStatusEnum::cases()),
        ];
    }

    public function withImages(int $count = 3): static
    {
        return $this->afterCreating(function (Banner $banner) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $banner->images()->create([
                    'path' => ImageFactory::placeholderUrl(),
                    'is_featured' => $i === 0,
                    'order' => $i,
                    'alt_text' => fake()->words(2, true),
                ]);
            }
        });
    }
}
