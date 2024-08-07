<?php

declare(strict_types=1);

use App\Filament\Resources\ProvinceResource;
use App\Models\Province;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the province resource.', function () {
    // Act & Assert
    get(ProvinceResource::getUrl())->assertOk();
});

it('can list provinces in the table.', function () {
    // Arrange
    $provinces = Province::factory()->count(5)->create();

    // Act & Assert
    livewire(ProvinceResource\Pages\ListProvinces::class)
        ->assertCanSeeTableRecords($provinces);
});

it('can render edit province page.', function () {
    // Act & Assert
    get(ProvinceResource::getUrl('edit', [
        'record' => Province::factory()->create(),
    ]))
        ->assertSuccessful();
});

it('can update province model.', function () {
    // Arrange
    $province = Province::factory()->create();
    $newProvince = Province::factory()->make();

    // Act & Assert
    livewire(ProvinceResource\Pages\EditProvince::class, [
        'record' => $province->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newProvince->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($province->refresh())
        ->name->toBe($newProvince->name);
});


it('can create province model.', function () {
    // Arrange
    $newProvince = Province::factory()->make();

    // Act & Assert
    livewire(ProvinceResource\Pages\CreateProvince::class)
        ->fillForm([
            'name' => $newProvince->name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Province::class, [
        'name' => $newProvince->name,
    ]);
});
