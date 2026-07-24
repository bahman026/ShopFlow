<?php

declare(strict_types=1);

use App\Enums\PageStatusEnum;
use App\Models\Page;
use Inertia\Testing\AssertableInertia;

it('renders a published CMS page by slug', function (): void {
    $page = Page::create([
        'heading' => 'درباره ما',
        'slug' => 'about-us',
        'content' => '<p>متن درباره ما</p>',
        'title' => 'درباره فروشگاه',
        'description' => 'معرفی فروشگاه',
        'status' => PageStatusEnum::PUBLISHED,
    ]);

    $this->get('/'.$page->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $assert): AssertableInertia => $assert
            ->component('Page/Show')
            ->where('page.heading', 'درباره ما')
            ->where('page.content', '<p>متن درباره ما</p>')
            ->has('breadcrumbs', 2)
            ->where('breadcrumbs.0.heading', 'خانه')
        );
});

it('returns 404 for a non-published page', function (): void {
    $page = Page::create([
        'heading' => 'پیش‌نویس',
        'slug' => 'draft-page',
        'status' => PageStatusEnum::DRAFT,
    ]);

    $this->get('/'.$page->slug)->assertNotFound();
});

it('returns 404 for a missing page', function (): void {
    $this->get('/does-not-exist')->assertNotFound();
});
