<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Brand;
use App\Observers\Concerns\FlushesCatalogCache;

/**
 * Flushes the catalog cache when a brand changes.
 *
 * A cached product page renders its brand live, as the `{heading, url}` link
 * `BuildProductDetail` reads off the relation. Product cards do not show a
 * brand, so before the product-page cache existed a rename needed no
 * invalidation at all.
 *
 * The delete case is the sharp one: `products.brand_id` is `nullOnDelete`, so
 * removing a brand rewrites every one of its products in the database without
 * firing a single Product event. Without this observer those pages would keep
 * advertising a brand that no longer exists for the rest of their TTL.
 */
class BrandObserver
{
    use FlushesCatalogCache;

    /**
     * `heading`/`slug` are the brand link on every product page of this brand.
     * `status` gates the brand page itself (`Brand::active()`) and the brand
     * facet a category listing offers.
     *
     * Deliberately absent: `content`, `title`, `description`, `no_index`,
     * `canonical` — brand-page-only copy, and that page is not cached.
     */
    private const array RENDERED_COLUMNS = ['heading', 'slug', 'status'];

    public function updated(Brand $brand): void
    {
        $this->flushWhenRenderedColumnsChanged($brand, self::RENDERED_COLUMNS);
    }

    public function deleted(Brand $brand): void
    {
        $this->flushForDelete();
    }
}
