<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

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
            'content' => fake()->paragraph(),
            'title' => fake()->text,
            'description' => fake()->text,
            'no_index' => 0,
            'canonical' => null,
            'parent_id' => null,
            'status' => CategoryStatusEnum::ACTIVE->value,
        ];
    }

    public function withImage(): BrandFactory | Factory
    {
        return $this->afterCreating(function (Category $category) {
            $category->image()->create([
                'path' => fake()->imageUrl(),
                'imageable_type' => Category::class,
                'imageable_id' => $category->id,
            ]);
        });
    }
}
