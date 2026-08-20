<?php

declare(strict_types=1);

use App\Enums\BannerPositionEnum;
use App\Enums\SliderPositionEnum;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\SlideResource;
use App\Models\Slider;

use function Pest\Laravel\get;

beforeEach(function () {
    login();
});

// Uploads are cropped to the ratio the slot actually renders at, so one
// oddly-shaped image cannot stretch the storefront layout.

it('gives every banner position a usable crop ratio and size', function () {
    foreach (BannerPositionEnum::cases() as $position) {
        expect($position->aspectRatio())->toMatch('/^\d+:\d+$/')
            ->and($position->recommendedSize())->toMatch('/^\d+ × \d+$/');
    }
});

it('gives every slider position a usable crop ratio and size', function () {
    foreach (SliderPositionEnum::cases() as $position) {
        expect($position->aspectRatio())->toMatch('/^\d+:\d+$/')
            ->and($position->recommendedSize())->toMatch('/^\d+ × \d+$/');
    }
});

it('matches the ratios the storefront components render at', function () {
    // Keep these in step with SliderSlot.vue / BannerSlot.vue.
    expect(SliderPositionEnum::HOME_MAIN->aspectRatio())->toBe('3:1')       // hero
        ->and(SliderPositionEnum::HOME_SECONDARY->aspectRatio())->toBe('4:1') // wide
        ->and(SliderPositionEnum::CATEGORY_TOP->aspectRatio())->toBe('4:1')   // wide
        ->and(SliderPositionEnum::PRODUCT_SIDE->aspectRatio())->toBe('4:5')   // portrait
        ->and(BannerPositionEnum::HOME_TOP->aspectRatio())->toBe('5:1')       // wide strip
        ->and(BannerPositionEnum::HOME_MIDDLE->aspectRatio())->toBe('16:9')   // grid
        ->and(BannerPositionEnum::CATEGORY_SIDE->aspectRatio())->toBe('4:5'); // sidebar stack
});

it('tells the admin the ratio and size once a banner position is chosen', function () {
    $hint = BannerResource::imageHint(BannerPositionEnum::HOME_MIDDLE);

    expect($hint)->toContain('16:9')->toContain('800 × 450')
        // A missing translation key would echo the key back.
        ->not->toContain('banner.path_hint');
});

it('asks for a position before it can name a banner ratio', function () {
    expect(BannerResource::imageHint(null))
        ->not->toBe('')
        ->not->toContain('banner.path_hint_no_position');
});

it('reads a slide ratio from the slider it belongs to', function () {
    $slider = Slider::factory()->create(['position' => SliderPositionEnum::PRODUCT_SIDE->value]);

    expect(SlideResource::positionOf($slider->id))->toBe(SliderPositionEnum::PRODUCT_SIDE)
        ->and(SlideResource::positionOf(null))->toBeNull()
        ->and(SlideResource::positionOf(999999))->toBeNull();

    expect(SlideResource::imageHint(SlideResource::positionOf($slider->id)))
        ->toContain('4:5')->toContain('600 × 750');
});

it('renders the banner and slide forms with the image editor enabled', function () {
    get(BannerResource::getUrl('create'))->assertOk();
    get(SlideResource::getUrl('create'))->assertOk();
});
