<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\Catalog\BuildProductCard;
use App\Enums\VarietyStatusEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetBrandProducts
{
    /**
     * Products shown per brand page.
     */
    private const PER_PAGE = 24;

    public function __construct(private BuildProductCard $buildProductCard) {}

    /**
     * Filtered, sorted, paginated product cards for a brand.
     *
     * @param  array{categories: array<int, string>, minPrice: int|null, maxPrice: int|null, inStock: bool, sort: string}  $filters
     * @return array{data: array<int, array<string, mixed>>, meta: array{currentPage: int, lastPage: int, perPage: int, total: int, from: int|null, to: int|null}}
     */
    public function __invoke(int $brandId, array $filters): array
    {
        $query = Product::query()
            ->published()
            ->where('brand_id', $brandId)
            ->with([
                'featuredImage',
                'varieties' => fn (Relation $relation) => $relation->where('status', VarietyStatusEnum::PUBLISHED->value)->with('image'),
            ]);

        if ($filters['categories'] !== []) {
            $query->whereHas('category', fn (Builder $category) => $category->whereIn('slug', $filters['categories']));
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
