<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Tag;
use App\Support\ProductCache;

/**
 * Flushes the cached product lists when a tag changes.
 *
 * Tags are the one piece of catalog metadata the home page reads as
 * *configuration*: `GetTagRows` renders one carousel per tag carrying
 * `show_on_home`, ordered by `home_order`. Nothing in a request for `/` reflects
 * that configuration, so the home page has no natural new cache key when it
 * changes — unlike the tag's own page, whose key already encodes the resolved
 * category and attribute ids and therefore self-heals when a tag is
 * reconfigured. Without this observer, featuring a tag would simply never appear
 * until the entry expired on its own.
 *
 * Three things make this differ from the other catalog-metadata observers, and
 * all three are deliberate:
 *
 * 1. **`saved` is hooked, and a create counts.** A brand-new tag with
 *    `show_on_home` set belongs on the home page immediately — the opposite of a
 *    new category or brand, which is attached to no product and so changes
 *    nothing anyone can see.
 * 2. **An empty change set flushes.** `TagResource` syncs the `attribute_tag`
 *    pivot after saving the record, which changes what the carousel matches
 *    while leaving every column on the tag itself clean.
 * 3. **Only the lists are flushed, never the product pages.** No product page
 *    renders a tag, so the `flushAll()` the other metadata observers use would
 *    cool the entire catalog for nothing.
 */
class TagObserver
{
    /**
     * Columns the home carousels render or select on: the title, the "view all"
     * link, whether the tag is featured at all, its position, and the category
     * half of what it matches.
     *
     * The SEO fields (`content`, `title`, `description`, `no_index`,
     * `canonical`) are absent on purpose — they render only on the tag's own
     * page, which is not cached.
     */
    private const array RENDERED_COLUMNS = [
        'name',
        'slug',
        'show_on_home',
        'home_order',
        'category_id',
    ];

    public function saved(Tag $tag): void
    {
        $this->invalidate($tag);
    }

    public function updated(Tag $tag): void
    {
        $this->invalidate($tag);
    }

    public function deleted(Tag $tag): void
    {
        ProductCache::flushLists();
    }

    private function invalidate(Tag $tag): void
    {
        $changed = array_keys($tag->getChanges());

        if ($changed !== [] && array_intersect($changed, self::RENDERED_COLUMNS) === []) {
            return;
        }

        ProductCache::flushLists();
    }
}
