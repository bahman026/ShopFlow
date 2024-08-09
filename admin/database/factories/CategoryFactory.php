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
            'no_index' => false,
            'canonical' => null,
            'parent_id' => null,
            'status' => CategoryStatusEnum::ACTIVE->value,
        ];
    }

    public function configure(): CategoryFactory | Factory
    {
        return $this->afterCreating(function (Category $category) {
            $category->imageable()->create([
                'path' => fake()->imageUrl(),
            ]);
        });
    }
}
