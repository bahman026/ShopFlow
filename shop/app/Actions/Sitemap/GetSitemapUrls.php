<?php

declare(strict_types=1);

namespace App\Actions\Sitemap;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

class GetSitemapUrls
{
    /**
     * Absolute, indexable URLs for the storefront sitemap: home, FAQ, then
     * active/published categories, brands, products and CMS pages. Records
     * flagged no_index are excluded.
     *
     * @return array<int, array{loc: string, lastmod: string|null}>
     */
    public function __invoke(): array
    {
        $base = rtrim((string) config('app.url'), '/');

        $static = [
            ['loc' => $base.'/', 'lastmod' => null],
            ['loc' => $base.'/faq', 'lastmod' => null],
        ];

        $categories = Category::query()
            ->active()
            ->where('no_index', false)
            ->get(['slug', 'updated_at'])
            ->map(fn (Category $category): array => [
                'loc' => $base.'/categories/'.$category->slug,
                'lastmod' => $category->updated_at?->toAtomString(),
            ])
            ->all();

        $brands = Brand::query()
            ->active()
            ->where('no_index', false)
            ->get(['slug', 'updated_at'])
            ->map(fn (Brand $brand): array => [
                'loc' => $base.'/brands/'.$brand->slug,
                'lastmod' => $brand->updated_at?->toAtomString(),
            ])
            ->all();

        $products = Product::query()
            ->published()
            ->where('no_index', false)
            ->get(['slug', 'updated_at'])
            ->map(fn (Product $product): array => [
                'loc' => $base.'/products/'.$product->slug,
                'lastmod' => $product->updated_at?->toAtomString(),
            ])
            ->all();

        $pages = Page::query()
            ->published()
            ->where('no_index', false)
            ->get(['slug', 'updated_at'])
            ->map(fn (Page $page): array => [
                'loc' => $base.'/'.$page->slug,
                'lastmod' => $page->updated_at?->toAtomString(),
            ])
            ->all();

        return [...$static, ...$categories, ...$brands, ...$products, ...$pages];
    }
}
