<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\TransformImage;
use App\Models\Category;

class GetHomeCategories
{
    public function __construct(private TransformImage $transformImage) {}

    /**
     * Top-level active categories for the quick-links strip.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return Category::query()
            ->active()
            ->whereNull('parent_id')
            ->with('image')
            ->orderBy('heading')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'heading' => $category->heading,
                'url' => '/categories/'.$category->slug,
                'image' => ($this->transformImage)($category->image)?->toArray(),
            ])
            ->all();
    }
}
