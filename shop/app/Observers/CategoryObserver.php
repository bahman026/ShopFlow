<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Observers\Concerns\FlushesCatalogCache;

/**
 * Flushes the catalog cache when a category changes.
 *
 * A cached product page renders its category live — the `{heading, url}` link in
 * `BuildProductDetail`, and the whole breadcrumb trail, which `BuildProductBreadcrumbs`
 * builds by walking `parent_id` upwards. So renaming a category changes every
 * product page in that category *and in every category beneath it*, which is
 * why this cannot be narrowed to one product.
 *
 * Product *cards* never show a category, so this was invisible before the
 * product-page cache existed: the rename simply showed up on the next render.
 */
class CategoryObserver
{
    use FlushesCatalogCache;

    /**
     * Columns the storefront renders off a category.
     *
     * `heading`/`slug` are the category link and every breadcrumb below it;
     * `parent_id` rewrites that trail wholesale; `status` decides whether the
     * category resolves at all (`Category::active()`), which also governs
     * whether its products are reachable through it.
     *
     * Deliberately absent: `content`, `title`, `description`, `no_index` and
     * `canonical`. Those render only on the category's own page, which is not
     * cached — editing category SEO copy must not cool the whole catalog.
     */
    private const array RENDERED_COLUMNS = ['heading', 'slug', 'parent_id', 'status'];

    public function updated(Category $category): void
    {
        $this->flushWhenRenderedColumnsChanged($category, self::RENDERED_COLUMNS);
    }

    public function deleted(Category $category): void
    {
        $this->flushForDelete();
    }
}
