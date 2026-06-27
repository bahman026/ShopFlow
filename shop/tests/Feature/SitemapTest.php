<?php

declare(strict_types=1);

use App\Enums\BrandStatusEnum;
use App\Enums\CategoryStatusEnum;
use App\Enums\PageStatusEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Page;

it('lists active and published entities in the sitemap', function (): void {
    $category = catCategory('map-shoes');
    $product = catProduct($category);

    $brand = Brand::create([
        'heading' => 'برند نقشه',
        'slug' => 'map-brand',
        'status' => BrandStatusEnum::ACTIVE,
    ]);

    $page = Page::create([
        'heading' => 'درباره ما',
        'slug' => 'map-about',
        'status' => PageStatusEnum::PUBLISHED,
    ]);

    $response = $this->get('/sitemap.xml')->assertOk();
    $response->assertHeader('content-type', 'application/xml; charset=UTF-8');

    $body = $response->getContent();

    expect($body)
        ->toContain('<urlset')
        ->toContain('/categories/'.$category->slug)
        ->toContain('/brands/'.$brand->slug)
        ->toContain('/products/'.$product->slug)
        ->toContain('/'.$page->slug)
        ->toContain('/faq');
});

it('excludes no_index and unpublished entities from the sitemap', function (): void {
    $hidden = Category::create([
        'heading' => 'دسته مخفی',
        'slug' => 'hidden-cat',
        'status' => CategoryStatusEnum::ACTIVE,
        'no_index' => true,
    ]);

    $draft = Page::create([
        'heading' => 'پیش‌نویس',
        'slug' => 'draft-map-page',
        'status' => PageStatusEnum::DRAFT,
    ]);

    $body = $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($body)
        ->not->toContain('/categories/'.$hidden->slug)
        ->not->toContain('/'.$draft->slug);
});

it('serves robots.txt pointing at the sitemap', function (): void {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertSee('Sitemap:', false)
        ->assertSee('/sitemap.xml', false);
});

it('returns 404 for unknown catalog slugs', function (): void {
    $this->get('/products/does-not-exist')->assertNotFound();
    $this->get('/categories/does-not-exist')->assertNotFound();
    $this->get('/brands/does-not-exist')->assertNotFound();
});
