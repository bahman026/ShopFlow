<?php

declare(strict_types=1);

use App\Enums\BannerPositionEnum;
use App\Enums\ImageAspectEnum;
use App\Enums\SliderPositionEnum;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\ReceiptResource;
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

// The fixed-shape slots (ImageAspectEnum). Banners and sliders are excluded on
// purpose: their shape comes from the position on the record, tested above.

it('gives every fixed image slot a usable size and upload ceiling', function () {
    foreach (ImageAspectEnum::cases() as $slot) {
        expect($slot->recommendedSize())->toMatch('/^\d+ × \d+$/')
            ->and($slot->maxSizeKb())->toBeGreaterThan(0);

        // A ratio is either absent by design or well-formed — never a typo.
        if ($slot->aspectRatio() !== null) {
            expect($slot->aspectRatio())->toMatch('/^\d+:\d+$/');
        }
    }
});

it('crops the slots the storefront draws in a fixed frame', function () {
    // Keep in step with ProductCard.vue / ProductGallery.vue (aspect-square),
    // CategoryStrip.vue (a rounded-full object-cover circle) and
    // Page/Show.vue (full width with no height frame of its own).
    expect(ImageAspectEnum::PRODUCT->aspectRatio())->toBe('1:1')
        ->and(ImageAspectEnum::VARIETY->aspectRatio())->toBe('1:1')
        ->and(ImageAspectEnum::CATEGORY->aspectRatio())->toBe('1:1')
        ->and(ImageAspectEnum::PAGE->aspectRatio())->toBe('16:9')
        ->and(ImageAspectEnum::TAG->aspectRatio())->toBe('16:9');
});

it('leaves logos and the payment receipt uncropped', function () {
    // Logos are drawn with object-contain, so a wide wordmark is already safe
    // and a forced square would cut it. A receipt is evidence: cropping can
    // remove the reference number, amount or date.
    expect(ImageAspectEnum::BRAND->aspectRatio())->toBeNull()
        ->and(ImageAspectEnum::GATEWAY->aspectRatio())->toBeNull()
        ->and(ImageAspectEnum::MENU_ITEM->aspectRatio())->toBeNull()
        ->and(ImageAspectEnum::RECEIPT->aspectRatio())->toBeNull();
});

it('keeps the product and variety forms on the same square', function () {
    // Two upload sites for the same photo — the repeater inside the product
    // form and the standalone variety form. They drifted apart before.
    expect(ImageAspectEnum::PRODUCT->aspectRatio())
        ->toBe(ImageAspectEnum::VARIETY->aspectRatio());
});

it('tells the admin the ratio and size for a cropped slot', function () {
    expect(ImageAspectEnum::CATEGORY->hint())
        ->toContain('1:1')->toContain('600 × 600')
        // A missing translation key would echo the key back.
        ->not->toContain('system.image_hint');
});

it('tells the admin an uncropped slot is kept whole', function () {
    expect(ImageAspectEnum::BRAND->hint())
        ->toContain('400 × 200')
        ->not->toContain('system.image_hint_free')
        // No ratio is named, because none is enforced.
        ->not->toContain(':');
});

it('renders every fixed-slot form with the new upload rules', function () {
    get(CategoryResource::getUrl('create'))->assertOk();
    get(BrandResource::getUrl('create'))->assertOk();
    get(PageResource::getUrl('create'))->assertOk();
    get(ReceiptResource::getUrl('create'))->assertOk();
});
