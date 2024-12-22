<?php

declare(strict_types=1);

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use App\Models\Image;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the brand resource.', function () {
    // Act & Assert
    get(BrandResource::getUrl())->assertOk();
});

it('can list brands in the table.', function () {
    // Arrange
    $brands = Brand::factory()->count(5)->create();

    // Act & Assert
    livewire(BrandResource\Pages\ListBrands::class)
        ->assertCanSeeTableRecords($brands);
});

it('can render edit brand page.', function () {
    // Act & Assert
    get(BrandResource::getUrl('edit', [
        'record' => Brand::factory()->create(),
    ]))
        ->assertSuccessful();
});

it('can update brand model.', function () {
    // Arrange
    $brand = Brand::factory()->withImage()->create();
    $newBrand = Brand::factory()->make();
    $file = UploadedFile::fake()->image('avatar.png', 500);

    // Act & Assert
    livewire(BrandResource\Pages\EditBrand::class, [
        'record' => $brand->getRouteKey(),
    ])
        ->fillForm([
            'heading' => $newBrand->heading,
            'slug' => $newBrand->slug,
            'title' => $newBrand->title,
            'content' => $newBrand->content,
            'description' => $newBrand->description,
            'no_index' => $newBrand->no_index,
            'canonical' => $newBrand->canonical,
            'status' => $newBrand->status,
        ])
        ->set('data.image.path', [$file->getClientOriginalPath()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($brand->refresh())
        ->heading->toBe($newBrand->heading)
        ->slug->toBe($newBrand->slug)
        ->title->toBe($newBrand->title)
        ->content->toBe($newBrand->content ? '<p>' . $newBrand->content . '</p>' : null)
        ->description->toBe($newBrand->description)
        ->no_index->toBe($newBrand->no_index)
        ->canonical->toBe($newBrand->canonical)
        ->status->toBe($newBrand->status);

    $brand = Brand::query()->where('slug', $newBrand->slug)->first();

    $this->assertDatabaseHas(Image::class, [
        'path' => $file->getClientOriginalPath(),
        'imageable_id' => $brand->id,
        'imageable_type' => Brand::class,
    ]);
});

it('can create brand model.', function () {
    // Arrange
    $newBrand = Brand::factory()->make();
    $file = UploadedFile::fake()->image('avatar.png', 500);

    livewire(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'heading' => $newBrand->heading,
            'slug' => $newBrand->slug,
            'title' => $newBrand->title,
            'content' => $newBrand->content,
            'description' => $newBrand->description,
            'no_index' => $newBrand->no_index,
            'canonical' => $newBrand->canonical,
            'status' => $newBrand->status->value,
        ])
        ->set('data.image.path', [$file->getClientOriginalPath()])
        ->call('create')
        ->assertHasNoFormErrors();

    //     Assert the database has the expected data
    $this->assertDatabaseHas(Brand::class, [
        'heading' => $newBrand->heading,
        'slug' => $newBrand->slug,
        'title' => $newBrand->title,
        'content' => $newBrand->content ? '<p>' . $newBrand->content . '</p>' : null,
        'description' => $newBrand->description,
        'no_index' => $newBrand->no_index,
        'canonical' => $newBrand->canonical,
        'status' => $newBrand->status->value,
    ]);

    $brand = Brand::query()->where('slug', $newBrand->slug)->first();

    $this->assertDatabaseHas(Image::class, [
        'path' => $file->getClientOriginalPath(),
        'imageable_id' => $brand->id,
        'imageable_type' => Brand::class,
    ]);
});

it('can delete brand model.', function () {
    // Arrange
    $brand = Brand::factory()->create();

    // Act & Assert
    livewire(BrandResource\Pages\EditBrand::class, [
        'record' => $brand->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);
    $this->assertModelMissing($brand);
});
