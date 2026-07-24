<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PageStatusEnum;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'heading' => fake()->words(4, true),
            'slug' => fn (array $attributes): string => Str::slug($attributes['heading']) . '-' . Str::uuid(),
            'content' => fake()->paragraphs(3, true),
            'title' => fake()->words(5, true),
            'description' => fake()->sentence(),
            'no_index' => fake()->boolean(20),
            'canonical' => null,
            'status' => fake()->randomElement(PageStatusEnum::cases()),
            'published_at' => null,
        ];
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (Page $page): void {
            $page->image()->create([
                'path' => ImageFactory::placeholderUrl(),
                'alt_text' => fake()->words(2, true),
            ]);
        });
    }
}
