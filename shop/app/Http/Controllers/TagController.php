<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\CollectCategoryIds;
use App\Actions\Category\GetCategoryFilters;
use App\Actions\Category\GetCategoryProducts;
use App\Actions\Tag\BuildTagBreadcrumbs;
use App\Actions\Tag\BuildTagDetail;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function show(
        string $slug,
        Request $request,
        CollectCategoryIds $collectCategoryIds,
        BuildTagDetail $buildTagDetail,
        BuildTagBreadcrumbs $buildBreadcrumbs,
        GetCategoryProducts $getCategoryProducts,
        GetCategoryFilters $getCategoryFilters,
    ): Response {
        $tag = Tag::query()
            ->where('slug', $slug)
            ->with(['category', 'attributes'])
            ->firstOrFail();

        // Category is optional: with one, scope to it (+ descendants); without,
        // no category constraint (an attribute-only tag spans all categories).
        $categoryIds = $tag->category === null ? [] : $collectCategoryIds($tag->category);

        $filters = $this->filters($request);

        // Force the tag's attribute(s) into the attribute filter so they're
        // always applied on top of any query-string filters. Grouping (OR
        // within a group, AND across groups) is handled by GetCategoryProducts.
        $tagAttributeIds = $tag->attributes->pluck('id')->all();
        $filters['attributes'] = array_values(array_unique([...$filters['attributes'], ...$tagAttributeIds]));

        return Inertia::render('Tags/Show', [
            'tag' => $buildTagDetail($tag)->toArray(),
            'breadcrumbs' => $buildBreadcrumbs($tag),
            'products' => $getCategoryProducts($categoryIds, $filters),
            'filters' => $getCategoryFilters($categoryIds, $filters),
            'applied' => $filters,
        ]);
    }

    /**
     * Normalise the filter/sort query parameters into a typed shape (same
     * shape the category page uses; the tag's own attribute is forced on top).
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
