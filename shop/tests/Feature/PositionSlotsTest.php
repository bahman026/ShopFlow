<?php

declare(strict_types=1);

use App\Enums\BannerPositionEnum;
use App\Enums\BannerStatusEnum;
use App\Enums\SliderPositionEnum;
use App\Enums\SliderStatusEnum;
use Inertia\Testing\AssertableInertia;

// Every BannerPositionEnum / SliderPositionEnum case has a render site. These
// tests hold that line: a slot fills when an admin assigns published content to
// it, and is empty — never missing, never erroring — when nothing is assigned.
//
// slotSlider / slotBanner / catCategory / catProduct live in tests/Helpers.php.

it('fills the home slots when content is assigned to them', function (): void {
    slotBanner(BannerPositionEnum::HOME_TOP);
    slotBanner(BannerPositionEnum::HOME_MIDDLE);
    slotSlider(SliderPositionEnum::HOME_MAIN);
    slotSlider(SliderPositionEnum::HOME_SECONDARY);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->has('topBanners', 1)
            ->has('banners', 1)
            ->has('slides', 1)
            ->has('secondarySlides', 1)
            ->where('topBanners.0.heading', 'بنر home-top')
            ->where('secondarySlides.0.heading', 'اسلاید home-secondary')
        );
});

it('leaves the home slots empty when nothing is assigned', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->has('topBanners', 0)
            ->has('secondarySlides', 0)
        );
});

it('fills the category slots when content is assigned to them', function (): void {
    $category = catCategory('slot-category');
    catProduct($category);

    slotSlider(SliderPositionEnum::CATEGORY_TOP);
    slotBanner(BannerPositionEnum::CATEGORY_SIDE);

    $this->get('/categories/'.$category->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Category/Show')
            ->has('topSlides', 1)
            ->has('sideBanners', 1)
            ->where('topSlides.0.heading', 'اسلاید category-top')
            ->where('sideBanners.0.heading', 'بنر category-side')
        );
});

it('leaves the category slots empty when nothing is assigned', function (): void {
    $category = catCategory('bare-category');
    catProduct($category);

    $this->get('/categories/'.$category->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('topSlides', 0)
            ->has('sideBanners', 0)
        );
});

it('fills the product slot when content is assigned to it', function (): void {
    $product = catProduct(catCategory('slot-product-category'));

    slotSlider(SliderPositionEnum::PRODUCT_SIDE);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Product/Show')
            ->has('sideSlides', 1)
            ->where('sideSlides.0.heading', 'اسلاید product-side')
        );
});

it('leaves the product slot empty when nothing is assigned', function (): void {
    $product = catProduct(catCategory('bare-product-category'));

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('sideSlides', 0)
        );
});

it('ignores unpublished content assigned to a slot', function (): void {
    slotSlider(SliderPositionEnum::HOME_SECONDARY, SliderStatusEnum::DRAFT);
    slotBanner(BannerPositionEnum::HOME_TOP, BannerStatusEnum::DRAFT);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('secondarySlides', 0)
            ->has('topBanners', 0)
        );
});

it('keeps each position independent of the others', function (): void {
    // Only the home hero is filled; no other slot may pick it up.
    slotSlider(SliderPositionEnum::HOME_MAIN);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('slides', 1)
            ->has('secondarySlides', 0)
        );

    $category = catCategory('independent-category');
    catProduct($category);

    $this->get('/categories/'.$category->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('topSlides', 0)
        );
});
