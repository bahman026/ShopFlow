<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Brand\BuildBrandBreadcrumbs;
use App\Actions\Brand\BuildBrandDetail;
use App\Actions\Brand\GetBrandFilters;
use App\Actions\Brand\GetBrandProducts;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function show(
        string $slug,
        Request $request,
        BuildBrandDetail $buildBrandDetail,
        BuildBrandBreadcrumbs $buildBreadcrumbs,
        GetBrandProducts $getBrandProducts,
        GetBrandFilters $getBrandFilters,
    ): Response {
        $brand = Brand::query()
            ->active()
            ->where('slug', $slug)
            ->with('image')
            ->firstOrFail();

        $filters = $this->filters($request);

        return Inertia::render('Brand/Show', [
            'brand' => $buildBrandDetail($brand)->toArray(),
            'breadcrumbs' => $buildBreadcrumbs($brand),
            'products' => $getBrandProducts($brand->id, $filters),
            'filters' => $getBrandFilters($brand->id, $filters),
            'applied' => $filters,
        ]);
    }

    /**
     * Normalise the filter/sort query parameters into a typed shape.
     *
     * @return array{categories: array<int, string>, minPrice: int|null, maxPrice: int|null, inStock: bool, sort: string}
     */
    private function filters(Request $request): array
    {
        $sort = (string) $request->query('sort', 'newest');

        if (! in_array($sort, ['newest', 'cheapest', 'expensive', 'popular'], true)) {
            $sort = 'newest';
        }

        return [
            'categories' => array_values(array_filter(array_map('strval', (array) $request->query('categories', [])))),
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
