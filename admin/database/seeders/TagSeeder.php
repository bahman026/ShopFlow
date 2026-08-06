<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Fake tags to generate for testing.
     */
    private const COUNT = 10;

    public function run(): void
    {
        Tag::query()->delete();

        $categories = Category::query()->inRandomOrder()->limit(self::COUNT)->get();
        $attributes = Attribute::query()->inRandomOrder()->limit(self::COUNT)->get();

        // With no reference data (e.g. run in isolation), let the factory make
        // its own throwaway category + attribute per tag so there is still
        // data to test against.
        if ($categories->isEmpty() || $attributes->isEmpty()) {
            Tag::factory()->count(self::COUNT)->withAttributes()->create();

            return;
        }

        // Normal path: fake tags (random name/slug/content) pointed at real
        // categories + a real attribute, so their /tags/{slug} pages can
        // resolve products instead of being empty. The first few are marked
        // "show on home" with an image so the storefront featured-tags strip
        // has data to render.
        for ($i = 0; $i < self::COUNT; $i++) {
            $factory = Tag::factory()->withAttributes([$attributes->random()->id]);

            if ($i < 4) {
                $factory = $factory->featured($i)->withImage();
            }

            $factory->create(['category_id' => $categories->random()->id]);
        }
    }
}
