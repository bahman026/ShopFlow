<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\BuildCategoryBreadcrumbs;
use App\Actions\Category\BuildCategoryDetail;
use App\Actions\Category\CollectCategoryIds;
use App\Actions\Category\GetCategoryFilters;
use App\Actions\Category\GetCategoryProducts;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function show(
        string $slug,
        Request $request,
        CollectCategoryIds $collectCategoryIds,
        BuildCategoryDetail $buildCategoryDetail,
        BuildCategoryBreadcrumbs $buildBreadcrumbs,
        GetCategoryProducts $getCategoryProducts,
        GetCategoryFilters $getCategoryFilters,
    ): Response {
        $category = Category::query()
            ->active()
            ->where('slug', $slug)
            ->with('image')
            ->firstOrFail();

        $categoryIds = $collectCategoryIds($category);
        $filters = $this->filters($request);

        return Inertia::render('Category/Show', [
            'category' => $buildCategoryDetail($category)->toArray(),
            'breadcrumbs' => $buildBreadcrumbs($category),
            'products' => $getCategoryProducts($categoryIds, $filters),
            'filters' => $getCategoryFilters($categoryIds, $filters),
            'applied' => $filters,
        ]);
    }

    /**
     * Normalise the filter/sort query parameters into a typed shape.
     *
     * @return array{brands: array<int, string>, attributes: array<int, int>, minPrice: int|null, maxPrice: int|null, inStock: bool, sort: string}
     */
    private function filters(Request $request): array
    {
        $sort = (string) $request->query('sort', 'newest');

        if (! in_array($sort, ['newest', 'cheapest', 'expensive', 'popular'], true)) {
            $sort = 'newest';
        }

        return [
            'brands' => array_values(array_filter(array_map('strval', (array) $request->query('brands', [])))),
            'attributes' => array_values(array_filter(array_map('intval', (array) $request->query('attributes', [])))),
            'minPrice' => $this->intOrNull($request->query('min_price')),
            'maxPrice' => $this->intOrNull($request->query('max_price')),
            'inStock' => $request->boolean('in_stock'),
            'sort' => $sort,
        ];
    }

    private function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
