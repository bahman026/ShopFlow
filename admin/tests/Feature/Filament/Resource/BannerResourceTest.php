<?php

declare(strict_types=1);

use App\Filament\Resources\BannerResource;
use App\Models\Banner;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the banner resource.', function () {
    get(BannerResource::getUrl())->assertOk();
});

it('can list banners in the table.', function () {
    $banners = Banner::factory()->count(5)->create();

    livewire(BannerResource\Pages\ListBanners::class)
        ->assertCanSeeTableRecords($banners);
});

it('can render edit banner page.', function () {
    $banner = Banner::factory()->create();

    get(BannerResource::getUrl('edit', [
        'record' => $banner,
    ]))->assertOk();
});

it('can update banner model.', function () {
    $banner = Banner::factory()->create();
    $newBanner = Banner::factory()->make();

    livewire(BannerResource\Pages\EditBanner::class, [
        'record' => $banner->getRouteKey(),
    ])
        ->fillForm([
            'position' => $newBanner->position,
            'heading' => $newBanner->heading,
            'url' => $newBanner->url,
            'sort' => $newBanner->sort,
            'status' => $newBanner->status->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($banner->refresh())
        ->position->toBe($newBanner->position)
        ->heading->toBe($newBanner->heading)
        ->url->toBe($newBanner->url)
        ->sort->toBe($newBanner->sort)
        ->status->toBe($newBanner->status);
});

it('can create banner model.', function () {
    $newBanner = Banner::factory()->make();

    livewire(BannerResource\Pages\CreateBanner::class)
        ->fillForm([
            'position' => $newBanner->position,
            'heading' => $newBanner->heading,
            'url' => $newBanner->url,
            'sort' => $newBanner->sort,
            'status' => $newBanner->status->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Banner::class, [
        'position' => $newBanner->position,
        'heading' => $newBanner->heading,
        'url' => $newBanner->url,
        'sort' => $newBanner->sort,
        'status' => $newBanner->status->value,
    ]);
});

it('can delete banner model.', function () {
    $banner = Banner::factory()->create();

    livewire(BannerResource\Pages\EditBanner::class, [
        'record' => $banner->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($banner);
});
