<?php

declare(strict_types=1);

use App\Filament\Resources\AttributeResource;
use App\Models\Attribute;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the attribute resource.', function () {
    // Act & Assert
    get(AttributeResource::getUrl())->assertOk();
});

it('can list attributes in the table.', function () {
    // Arrange
    $attributes = Attribute::factory()->count(5)->create();

    // Act & Assert
    livewire(AttributeResource\Pages\ListAttributes::class)
        ->assertCanSeeTableRecords($attributes);
});

it('can render edit attribute page.', function () {
    // Act & Assert
    get(AttributeResource::getUrl('edit', [
        'record' => Attribute::factory()->create(),
    ]))
        ->assertSuccessful();
});

it('can update attribute model.', function () {
    // Arrange
    $attribute = Attribute::factory()->create();
    $newAttribute = Attribute::factory()->make();

    // Act & Assert
    livewire(AttributeResource\Pages\EditAttribute::class, [
        'record' => $attribute->getRouteKey(),
    ])
        ->fillForm([
            'attribute_group_id' => $newAttribute->attribute_group_id,
            'value' => $newAttribute->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($attribute->refresh())
        ->attribute_group_id->toBe($newAttribute->attribute_group_id)
        ->value->toBe($newAttribute->value);
});

it('can create attribute model.', function () {
    // Arrange
    $newAttribute = Attribute::factory()->make();

    livewire(AttributeResource\Pages\CreateAttribute::class)
        ->fillForm([
            'attribute_group_id' => $newAttribute->attribute_group_id,
            'value' => $newAttribute->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    //     Assert the database has the expected data
    $this->assertDatabaseHas(Attribute::class, [
        'attribute_group_id' => $newAttribute->attribute_group_id,
        'value' => $newAttribute->value,
    ]);

});

it('cannot delete attribute model.', function () {
    // Arrange
    $attribute = Attribute::factory()->create();

    // Act & Assert
    livewire(AttributeResource\Pages\EditAttribute::class, [
        'record' => $attribute->getRouteKey(),
    ])
        ->assertActionDoesNotExist(DeleteAction::class);
});
