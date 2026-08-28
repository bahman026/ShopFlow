<?php

declare(strict_types=1);

namespace App\Observers\Concerns;

use App\Models\Product;
use App\Support\ProductCache;

/**
 * Shared by the observers on the tables a cached product payload is *built
 * from* — varieties, images, reviews. Each of those rows knows a product id,
 * while `ProductCache` keys by slug (the storefront URL carries the slug), so
 * the id has to be resolved before anything can be forgotten.
 *
 * Mirrored between both apps; see `App\Support\ProductCache`.
 */
trait ForgetsProductCache
{
    /**
     * Forget the cached pages of the given products, and optionally every
     * cached product list.
     *
     * `$affectsCards` is the caller's judgement about whether the change is
     * visible on a product *card*. Inventory is the case worth knowing: it
     * appears on a product page and on no card, so selling the last unit
     * refreshes one page and leaves every category listing warm.
     *
     * @param  array<int, int|null>  $productIds
     */
    protected function forgetProducts(array $productIds, bool $affectsCards): void
    {
        $ids = array_values(array_unique(array_filter($productIds)));

        if ($ids === []) {
            return;
        }

        // A cascade delete removes child rows without Eloquent events, so a
        // product may already be gone by the time we look: no slug, nothing to
        // forget, and the product's own `deleted` observer handled it anyway.
        $slugs = Product::query()
            ->whereKey($ids)
            ->pluck('slug')
            ->filter()
            ->map(fn (mixed $slug): string => (string) $slug)
            ->all();

        ProductCache::forgetProduct(...$slugs);

        if ($affectsCards) {
            ProductCache::flushLists();
        }
    }
}
