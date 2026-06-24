<?php

declare(strict_types=1);

use App\Enums\PageStatusEnum;
use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the page resource.', function () {
    get(PageResource::getUrl())->assertOk();
});

it('can list pages in the table.', function () {
    $pages = Page::factory()->count(5)->create();

    livewire(PageResource\Pages\ListPages::class)
        ->assertCanSeeTableRecords($pages);
});

it('can render edit page.', function () {
    $page = Page::factory()->create();

    get(PageResource::getUrl('edit', [
        'record' => $page,
    ]))->assertOk();
});

it('can update page model.', function () {
    $page = Page::factory()->create();
    $newPage = Page::factory()->make(['status' => PageStatusEnum::PUBLISHED]);

    livewire(PageResource\Pages\EditPage::class, [
        'record' => $page->getRouteKey(),
    ])
        ->fillForm([
            'heading' => $newPage->heading,
            'slug' => $newPage->slug,
            'title' => $newPage->title,
            'description' => $newPage->description,
            'no_index' => $newPage->no_index,
            'status' => PageStatusEnum::PUBLISHED->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh())
        ->heading->toBe($newPage->heading)
        ->slug->toBe($newPage->slug)
        ->status->toBe(PageStatusEnum::PUBLISHED);
});

it('can create page model.', function () {
    $newPage = Page::factory()->make(['status' => PageStatusEnum::PUBLISHED]);

    livewire(PageResource\Pages\CreatePage::class)
        ->fillForm([
            'heading' => $newPage->heading,
            'slug' => $newPage->slug,
            'title' => $newPage->title,
            'description' => $newPage->description,
            'no_index' => $newPage->no_index,
            'status' => PageStatusEnum::PUBLISHED->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Page::class, [
        'heading' => $newPage->heading,
        'slug' => $newPage->slug,
        'status' => PageStatusEnum::PUBLISHED->value,
    ]);
});

it('can delete page model.', function () {
    $page = Page::factory()->create();

    livewire(PageResource\Pages\EditPage::class, [
        'record' => $page->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($page);
});

it('deletes image when page is deleted.', function () {
    $page = Page::factory()->withImage()->create();
    $image = $page->image;

    livewire(PageResource\Pages\EditPage::class, [
        'record' => $page->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($page);
    $this->assertModelMissing($image);
});
