<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use App\Support\ProductCache;

/**
 * Drops cached product payloads when a product row changes.
 *
 * Registered in **both** apps, because both write this table: the panel edits
 * the catalog, the storefront bumps the view counter. Whichever app performs a
 * write is the one that has to invalidate — an admin-only observer would leave
 * the storefront's own writes serving stale pages.
 */
class ProductObserver
{
    /**
     * Columns a product *card* renders, or a listing filters/sorts on. Only
     * these can change what a category page shows, so only these justify the
     * blunt list-wide invalidation. Editing `content`, the SEO fields or the
     * package dimensions changes the product's own page and nothing else.
     */
    private const array CARD_COLUMNS = [
        'heading',
        'slug',
        'price',
        'has_stock',
        'status',
        'category_id',
        'brand_id',
        'image_id',
    ];

    /**
     * `ProductController@show` (storefront) bumps `products.seen` on every
     * product-page view. Treating that as a content change would delete the
     * entry the same request had just written, on every single visit — the
     * cache would look implemented and never serve anything from cache.
     */
    private const array IGNORED_COLUMNS = ['seen', 'updated_at'];

    public function saved(Product $product): void
    {
        $this->invalidate($product);
    }

    /**
     * Both `saved` and `updated` are hooked on purpose, because neither alone
     * sees every write: `increment()`/`decrement()` fire only `updated`, while
     * a pivot-only edit in Filament (`product_attribute` synced after the
     * record itself came out clean) fires only `saved`. On an ordinary save
     * both fire and the duplicated work is a second Redis delete.
     */
    public function updated(Product $product): void
    {
        $this->invalidate($product);
    }

    public function deleted(Product $product): void
    {
        ProductCache::forgetProduct($product->slug, $this->originalSlug($product));
        ProductCache::flushLists();
    }

    private function invalidate(Product $product): void
    {
        $changed = array_keys($product->getChanges());

        if ($changed !== [] && array_diff($changed, self::IGNORED_COLUMNS) === []) {
            return;
        }

        ProductCache::forgetProduct($product->slug, $this->originalSlug($product));

        // No recorded change means the row itself was clean and something
        // beside it moved (a synced pivot), so what changed is unknowable from
        // here — assume the worst and refresh the lists as well.
        if ($changed === [] || array_intersect($changed, self::CARD_COLUMNS) !== []) {
            ProductCache::flushLists();
        }
    }

    /**
     * A renamed slug would otherwise leave an entry under the old URL that
     * nothing ever deletes. Eloquent syncs originals *after* `saved`/`updated`
     * fire, so the pre-save value is still readable here.
     */
    private function originalSlug(Product $product): ?string
    {
        $slug = $product->getOriginal('slug');

        return is_string($slug) ? $slug : null;
    }
}
