<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Actions\Catalog\BuildProductCard;
use App\Enums\VarietyStatusEnum;
use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class GetCategoryProducts
{
    /**
     * Products shown per category page.
     */
    private const PER_PAGE = 24;

    public function __construct(private BuildProductCard $buildProductCard) {}

    /**
     * Filtered, sorted, paginated product cards for a category.
     *
     * @param  array<int, int>  $categoryIds
     * @param  array{brands: array<int, string>, attributes: array<int, int>, minPrice: int|null, maxPrice: int|null, inStock: bool, sort: string}  $filters
     * @return array{data: array<int, array<string, mixed>>, meta: array{currentPage: int, lastPage: int, perPage: int, total: int, from: int|null, to: int|null}}
     */
    public function __invoke(array $categoryIds, array $filters): array
    {
        $query = Product::query()
            ->published()
            ->whereIn('category_id', $categoryIds)
            ->with([
                'featuredImage',
                'varieties' => fn (Relation $relation) => $relation->where('status', VarietyStatusEnum::PUBLISHED->value)->with('image'),
            ]);

        if ($filters['brands'] !== []) {
            $query->whereHas('brand', fn (Builder $brand) => $brand->whereIn('slug', $filters['brands']));
        }

        if ($filters['minPrice'] !== null) {
            $query->where('price', '>=', $filters['minPrice']);
        }

        if ($filters['maxPrice'] !== null) {
            $query->where('price', '<=', $filters['maxPrice']);
        }

        if ($filters['inStock']) {
            $query->where('has_stock', true);
        }

        // Faceted attribute filter through the product↔attribute pivot
        // (product_attribute is the documented "filters to products" link).
        // OR within a group, AND across groups.
        foreach ($this->groupedAttributes($filters['attributes']) as $attributeIds) {
            $query->whereHas('attributes', fn (Builder $attribute) => $attribute->whereIn('attributes.id', $attributeIds));
        }

        $this->applySort($query, $filters['sort']);

        $paginator = $query->paginate(self::PER_PAGE)->withQueryString();

        /** @var array<int, Product> $items */
        $items = $paginator->items();

        return [
            'data' => array_map(fn (Product $product): array => ($this->buildProductCard)($product), $items),
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * Group selected attribute ids by their attribute group, so each group
     * becomes one AND-ed constraint while values inside it stay OR-ed.
     *
     * @param  array<int, int>  $attributeIds
     * @return array<int, array<int, int>>
     */
    private function groupedAttributes(array $attributeIds): array
    {
        if ($attributeIds === []) {
            return [];
        }

        return Attribute::query()
            ->whereIn('id', $attributeIds)
            ->get()
            ->groupBy('attribute_group_id')
            ->map(fn (Collection $group): array => $group->pluck('id')->all())
            ->values()
            ->all();
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'cheapest' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('seen'),
            default => $query->orderByDesc('id'),
        };
    }
}
