<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\BuildProductCard;
use App\Enums\VarietyStatusEnum;
use App\Models\Product;
use App\Support\ProductCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetProductRows
{
    /**
     * How many items each product carousel shows.
     */
    private const ROW_LIMIT = 12;

    public function __construct(private BuildProductCard $buildProductCard) {}

    /**
     * Two product carousels: newest and most viewed. Empty rows are dropped.
     *
     * Cached (`CACHE.md` key 13). Nothing about this varies per visitor or per
     * request, so the signature carries only the locale — the row titles come
     * from `trans()`, and a cached Persian payload must not be served to a
     * future English request.
     *
     * The `popular` row sorts on `products.seen`, which every product-page view
     * increments. That deliberately does not invalidate anything: `seen` is in
     * `ProductObserver`'s ignored list, so this row lags the true view counts by
     * up to its TTL. That is the right trade — a "most viewed" carousel reordering
     * itself on every page view would be both pointless and ruinous for the cache.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return ProductCache::rememberList(
            'home.rows',
            ['locale' => app()->getLocale()],
            fn (): array => $this->fetch(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetch(): array
    {
        $rows = [
            ['title' => trans('messages.home.row_newest'), 'viewAllUrl' => '/products?sort=newest', 'query' => fn (Builder $q) => $q->latest('id')],
            ['title' => trans('messages.home.row_popular'), 'viewAllUrl' => '/products?sort=popular', 'query' => fn (Builder $q) => $q->orderByDesc('seen')],
        ];

        return array_values(array_filter(array_map(function (array $row): array {
            $products = Product::query()
                ->published()
                ->with(['featuredImage', 'varieties' => fn (Relation $q) => $q->where('status', VarietyStatusEnum::PUBLISHED->value)->with('image')])
                ->tap($row['query'])
                ->limit(self::ROW_LIMIT)
                ->get()
                ->map(fn (Product $product): array => ($this->buildProductCard)($product))
                ->all();

            return [
                'title' => $row['title'],
                'viewAllUrl' => $row['viewAllUrl'],
                'products' => $products,
            ];
        }, $rows), fn (array $row): bool => $row['products'] !== []));
    }
}
