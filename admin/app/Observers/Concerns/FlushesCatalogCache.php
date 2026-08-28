<?php

declare(strict_types=1);

namespace App\Observers\Concerns;

use App\Support\ProductCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared by the observers on catalog *metadata* — categories, brands,
 * attributes and attribute groups.
 *
 * ## Why these flush instead of forgetting
 *
 * The `ForgetsProductCache` observers can be precise: a variety knows its
 * product, so it forgets that one page. Metadata cannot. The products showing a
 * renamed attribute are reachable only through three relations at once
 * (`product_attribute` for the spec table, `varieties.attribute_id` for the
 * primary variant axis, and the `attribute_variety` pivot for the secondary
 * ones), a renamed category reaches every product in its whole descendant
 * subtree through the breadcrumb trail, and for something like the "رنگ"
 * attribute group that set is most of the catalog. Enumerating it would mean
 * thousands of `Cache::forget()` round trips inside one admin save — strictly
 * worse than the single generation bump `flushAll()` performs.
 *
 * That trade is affordable because these are rare: renaming a category or an
 * attribute is an occasional metadata edit, not a per-order write. The frequent
 * writes (a sale moving `inventory`, a price change) stay precise, on the
 * observers that can afford to be.
 *
 * ## Why `updated`, and not `saved` like the product observers
 *
 * `ProductObserver` and `VarietyObserver` hook both, because a Filament
 * pivot-only edit fires just `saved` and `increment()` fires just `updated`.
 * Neither applies here, and `saved` brings a real hazard with it: it also fires
 * for an insert, where `getChanges()` is empty and a conservative
 * "unknown change, flush anyway" rule would flush the catalog once per row of
 * every seeder run. `updated` fires only when the row was genuinely dirty, so an
 * insert cannot reach these observers and a new category — attached to no
 * product, and therefore in nothing cached — correctly changes nothing.
 *
 * The gap that leaves is `attribute_group_category.as_filter`, a pivot edited as
 * its own entity in the panel. It feeds only the not-yet-built category-filter
 * cache, so it is deferred rather than forgotten — see `docs/CACHE.md`.
 *
 * ## Why a delete always flushes
 *
 * Deleting metadata changes what product pages render *without firing a single
 * Eloquent event on those products*, because the fixups happen in the database:
 * `products.brand_id`, `products.attribute_group_id` and
 * `varieties.attribute_id` are all `nullOnDelete`, and `product_attribute` /
 * `attribute_variety` both `cascadeOnDelete`. Nothing observes any of that, so
 * the flush here is the only thing standing between a deleted brand and a
 * cached page still advertising it.
 *
 * `Category` is the one case where this is belt-and-braces rather than strictly
 * required — both `products.category_id` and `categories.parent_id` are
 * `restrictOnDelete`, so only a childless, product-less category can be
 * removed, which no cached product page can be showing. It flushes anyway, so
 * that "metadata delete flushes" holds without a reader having to re-audit six
 * foreign keys, and so a later change to those rules cannot quietly break it.
 *
 * Mirrored between both apps; see `App\Support\ProductCache`.
 */
trait FlushesCatalogCache
{
    /**
     * Flush every cached product page and list, but only when the update
     * actually touched a column the storefront renders.
     *
     * @param  array<int, string>  $renderedColumns
     */
    protected function flushWhenRenderedColumnsChanged(Model $model, array $renderedColumns): void
    {
        if (array_intersect(array_keys($model->getChanges()), $renderedColumns) === []) {
            return;
        }

        ProductCache::flushAll();
    }

    protected function flushForDelete(): void
    {
        ProductCache::flushAll();
    }
}
