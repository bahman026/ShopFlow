<?php

declare(strict_types=1);

use App\Enums\HomeSectionTypeEnum;
use App\Filament\Resources\HomeSectionResource;
use App\Filament\Resources\HomeSectionResource\Pages\CreateHomeSection;
use App\Filament\Resources\HomeSectionResource\Pages\EditHomeSection;
use App\Filament\Resources\HomeSectionResource\Pages\ListHomeSections;
use App\Models\HomeSection;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the home section resource.', function () {
    get(HomeSectionResource::getUrl())->assertOk();
});

it('can list home sections in the table.', function () {
    $sections = HomeSection::factory()->count(3)->create();

    livewire(ListHomeSections::class)
        ->assertCanSeeTableRecords($sections);
});

it('can create a product-row section with a sort and title.', function () {
    livewire(CreateHomeSection::class)
        ->fillForm([
            'type' => HomeSectionTypeEnum::PRODUCTS->value,
            'title' => 'جدیدترین محصولات',
            'config' => ['sort' => 'newest'],
            'status' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $section = HomeSection::query()->latest('id')->firstOrFail();
    expect($section->type)->toBe(HomeSectionTypeEnum::PRODUCTS)
        ->and($section->config)->toBe(['sort' => 'newest']);
});

it('can create a slider section with a position.', function () {
    livewire(CreateHomeSection::class)
        ->fillForm([
            'type' => HomeSectionTypeEnum::SLIDER->value,
            'config' => ['position' => 'home-main'],
            'status' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(HomeSection::query()->latest('id')->firstOrFail()->config)->toBe(['position' => 'home-main']);
});

it('requires a position for a slider section.', function () {
    livewire(CreateHomeSection::class)
        ->fillForm([
            'type' => HomeSectionTypeEnum::SLIDER->value,
            'config' => ['position' => null],
            'status' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['config.position']);
});

it('can delete a home section.', function () {
    $section = HomeSection::factory()->create();

    livewire(EditHomeSection::class, ['record' => $section->getRouteKey()])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($section);
});
