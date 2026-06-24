<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReviewStatusEnum;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'heading' => fake()->sentence(5),
            'content' => fake()->paragraph(),
            'user_id' => null,
            'product_id' => Product::factory(),
            'variety_id' => null,
            'parent_id' => null,
            'status' => fake()->randomElement(ReviewStatusEnum::cases()),
        ];
    }

    public function withParent(Review $parent): static
    {
        return $this->state([
            'parent_id' => $parent->id,
            'product_id' => $parent->product_id,
        ]);
    }
}
