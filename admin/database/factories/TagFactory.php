<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            // Persian names slugify to empty, so use a uuid for a unique slug
            // (see AGENTS.md → Factories).
            'slug' => (string) Str::uuid(),
            // A category by default so the tag is valid ("at least one of
            // category/attributes"); override to null for attribute-only tags.
            'category_id' => Category::factory(),
            'content' => fake()->optional()->paragraph(),
            'title' => fake()->optional()->sentence(),
            'description' => fake()->optional()->sentence(),
            'no_index' => false,
            'canonical' => null,
        ];
    }

    /**
     * Attach attributes to the tag (a new one is created if none given).
     *
     * @param  array<int, int>|null  $attributeIds
     */
    public function withAttributes(?array $attributeIds = null, int $count = 1): static
    {
        return $this->afterCreating(function (Tag $tag) use ($attributeIds, $count): void {
            $ids = $attributeIds ?? Attribute::factory()->count($count)->create()->pluck('id')->all();
            $tag->attributes()->syncWithoutDetaching($ids);
        });
    }

    /**
     * Show the tag in the storefront home-page featured-tags strip.
     */
    public function featured(int $order = 0): static
    {
        return $this->state([
            'show_on_home' => true,
            'home_order' => $order,
        ]);
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (Tag $tag): void {
            $tag->image()->create([
                'path' => ImageFactory::placeholderUrl(),
                'is_featured' => true,
                'order' => 0,
                'alt_text' => is_string($tag->name) ? $tag->name : null,
            ]);
        });
    }
}
