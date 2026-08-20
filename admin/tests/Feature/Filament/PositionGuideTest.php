<?php

declare(strict_types=1);

use App\Enums\BannerPositionEnum;
use App\Enums\SliderPositionEnum;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\Resources\SliderResource;
use App\Filament\Resources\SliderResource\Pages\CreateSlider;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Models\Banner;
use App\Models\Slider;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

// Position values are opaque strings ('product-side'). The form has to say
// where each one lands, otherwise picking one is guesswork.

it('gives every banner position a label, a description and a page', function () {
    foreach (BannerPositionEnum::cases() as $position) {
        expect($position->label())->not->toBe('')
            ->and($position->description())->not->toBe('')
            // A missing key makes trans() echo the key back.
            ->and($position->description())->not->toContain('banner.position_')
            ->and($position->page())->toBeIn(['home', 'category', 'product']);
    }
});

it('gives every slider position a label, a description and a page', function () {
    foreach (SliderPositionEnum::cases() as $position) {
        expect($position->label())->not->toBe('')
            ->and($position->description())->not->toBe('')
            ->and($position->description())->not->toContain('slider.position_')
            ->and($position->page())->toBeIn(['home', 'category', 'product']);
    }
});

it('offers a description for every option the banner form lists', function () {
    expect(array_keys(BannerPositionEnum::descriptions()))
        ->toBe(array_keys(BannerPositionEnum::options()));
});

it('offers a description for every option the slider form lists', function () {
    expect(array_keys(SliderPositionEnum::descriptions()))
        ->toBe(array_keys(SliderPositionEnum::options()));
});

it('renders the layout wireframe on the banner create form', function () {
    get(BannerResource::getUrl('create'))
        ->assertOk()
        ->assertSee('pg__slot', escape: false)
        ->assertSee(trans('position_guide.page_home'))
        ->assertSee(trans('position_guide.page_category'))
        ->assertSee(BannerPositionEnum::HOME_TOP->description());
});

it('renders the layout wireframe on the slider create form', function () {
    get(SliderResource::getUrl('create'))
        ->assertOk()
        ->assertSee('pg__slot', escape: false)
        ->assertSee(trans('position_guide.page_product'))
        ->assertSee(SliderPositionEnum::PRODUCT_SIDE->description());
});

it('highlights the saved position when editing a banner', function () {
    $banner = Banner::factory()->create(['position' => BannerPositionEnum::CATEGORY_SIDE->value]);

    get(BannerResource::getUrl('edit', ['record' => $banner]))
        ->assertOk()
        // The wrapper attribute drives the highlight ...
        ->assertSee('data-selected="category-side"', escape: false)
        // ... and the slot it points at has to exist.
        ->assertSee('data-slot="category-side"', escape: false);
});

it('highlights the saved position when editing a slider', function () {
    $slider = Slider::factory()->create(['position' => SliderPositionEnum::PRODUCT_SIDE->value]);

    get(SliderResource::getUrl('edit', ['record' => $slider]))
        ->assertOk()
        ->assertSee('data-selected="product-side"', escape: false)
        ->assertSee('data-slot="product-side"', escape: false);
});

it('emits a highlight rule for every position of both kinds', function () {
    $html = get(BannerResource::getUrl('create'))->getContent();

    foreach ([...BannerPositionEnum::cases(), ...SliderPositionEnum::cases()] as $position) {
        expect($html)
            ->toContain('data-slot="' . $position->value . '"')
            ->toContain('.pg[data-selected="' . $position->value . '"]');
    }
});

// Filament wraps every schema component in a wire:partial. The guide's own
// state never changes, so without an explicit partial re-render the browser
// keeps showing the stale wireframe even though the server renders the right
// one. Filament throws if the named component cannot be resolved, so simply
// changing the position is enough to catch a rename or a typo here.
it('re-renders the wireframe when the banner position changes', function () {
    livewire(CreateBanner::class)
        ->set('data.position', BannerPositionEnum::HOME_TOP->value)
        ->assertSee('data-selected="home-top"', escape: false);
});

it('re-renders the wireframe when the slider position changes', function () {
    livewire(CreateSlider::class)
        ->set('data.position', SliderPositionEnum::PRODUCT_SIDE->value)
        ->assertSee('data-selected="product-side"', escape: false);
});

it('keeps the guide in step with the radio without a server round-trip', function () {
    // Alpine mirrors the radio into data-selected, so a stale wire:partial can
    // never leave the wireframe showing the wrong slot.
    get(BannerResource::getUrl('create'))
        ->assertOk()
        ->assertSee('x-bind:data-selected', escape: false);
});

it('never writes the wireframe field to the model', function () {
    expect(Banner::factory()->create()->getAttributes())->not->toHaveKey('position_guide')
        ->and(Slider::factory()->create()->getAttributes())->not->toHaveKey('position_guide');
});

// Featured tags have no position column: they always land in the same slot on
// the home page. The guide appears once the toggle is on, to say where.

it('hides the tag layout guide until the homepage toggle is on', function () {
    livewire(CreateTag::class)
        ->set('data.show_on_home', false)
        ->assertDontSee('data-slot="home-tags"', escape: false);
});

it('shows the tag layout guide when the homepage toggle is on', function () {
    livewire(CreateTag::class)
        ->set('data.show_on_home', true)
        ->assertSee('data-slot="home-tags"', escape: false)
        ->assertSee('data-selected="home-tags"', escape: false);
});

it('dims the pages a featured tag never appears on', function () {
    $html = livewire(CreateTag::class)
        ->set('data.show_on_home', true)
        ->html();

    // Home holds the slot; category and product do not.
    expect(substr_count($html, 'pg__card--muted'))->toBeGreaterThan(1);
});
