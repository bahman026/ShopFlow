<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Models\Category;
use App\Models\Product;

class GetBrandFilters
{
    /**
     * Available facets for the brand: the categories its products fall into
     * (with counts) and the product price bounds.
     *
     * @param  array{categories: array<int, string>, minPrice: int|null, maxPrice: int|null, inStock: bool, sort: string}  $filters
     * @return array{categories: array<int, array<string, mixed>>, price: array{min: int, max: int}}
     */
    public function __invoke(int $brandId, array $filters): array
    {
        return [
            'categories' => $this->categories($brandId, $filters['categories']),
            'price' => $this->priceBounds($brandId),
        ];
    }

    /**
     * @param  array<int, string>  $selected
     * @return array<int, array<string, mixed>>
     */
    private function categories(int $brandId, array $selected): array
    {
        $counts = Product::query()
            ->published()
            ->where('brand_id', $brandId)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, count(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        return Category::query()
            ->active()
            ->whereIn('id', $counts->keys()->all())
            ->orderBy('heading')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'heading' => $category->heading,
                'slug' => $category->slug,
                'count' => (int) ($counts[$category->id] ?? 0),
                'selected' => in_array($category->slug, $selected, true),
            ])
            ->all();
    }

    /**
     * @return array{min: int, max: int}
     */
    private function priceBounds(int $brandId): array
    {
        $base = Product::query()
            ->published()
            ->where('brand_id', $brandId);

        return [
            'min' => (int) ((clone $base)->min('price') ?? 0),
            'max' => (int) ((clone $base)->max('price') ?? 0),
        ];
    }
}
