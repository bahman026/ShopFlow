<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Slide;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slide>
 */
class SlideFactory extends Factory
{
    protected $model = Slide::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slider_id' => Slider::factory(),
            'heading' => fake()->words(4, true),
            'label' => fake()->optional()->words(3, true),
            'url' => fake()->optional()->url(),
            'order' => fake()->numberBetween(0, 20),
        ];
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (Slide $slide): void {
            $slide->image()->create([
                'path' => fake()->imageUrl(),
                'is_featured' => true,
                'order' => 0,
                'alt_text' => $slide->heading,
            ]);
        });
    }
}
