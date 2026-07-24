<?php

declare(strict_types=1);

namespace App\Actions\Search;

use App\Contracts\ProductSearch;
use App\Models\Category;

class GetSearchSuggestions
{
    public function __construct(private ProductSearch $search) {}

    /**
     * Autocomplete payload for the header search: matching categories plus
     * matching products (with their category as context).
     *
     * @return array{categories: array<int, array{heading: string, url: string}>, products: array<int, array{heading: string, url: string, categoryHeading: string|null}>}
     */
    public function __invoke(string $term): array
    {
        $term = trim($term);

        if ($term === '') {
            return ['categories' => [], 'products' => []];
        }

        $categories = Category::query()
            ->active()
            ->where('heading', 'ilike', '%'.$term.'%')
            ->orderBy('heading')
            ->limit(5)
            ->get(['heading', 'slug'])
            ->map(fn (Category $category): array => [
                'heading' => $category->heading,
                'url' => '/categories/'.$category->slug,
            ])
            ->all();

        return [
            'categories' => $categories,
            'products' => $this->search->suggest($term, 8),
        ];
    }
}
