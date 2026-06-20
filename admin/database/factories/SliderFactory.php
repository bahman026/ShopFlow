<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SliderStatusEnum;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slider>
 */
class SliderFactory extends Factory
{
    protected $model = Slider::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'position' => fake()->randomElement(['home-main', 'home-secondary', 'category-top', 'product-side']),
            'status' => fake()->randomElement(SliderStatusEnum::cases()),
        ];
    }
}
