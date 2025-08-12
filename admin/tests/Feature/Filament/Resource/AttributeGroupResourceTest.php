<?php

declare(strict_types=1);

use App\Filament\Resources\AttributeGroupResource;
use App\Models\AttributeGroup;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the attributeGroup resource.', function () {
    // Act & Assert
    get(AttributeGroupResource::getUrl())->assertOk();
});

it('can list attributeGroups in the table.', function () {
    // Arrange
    $attributeGroups = AttributeGroup::factory()->count(5)->create();

    // Act & Assert
    livewire(AttributeGroupResource\Pages\ListAttributeGroups::class)
        ->assertCanSeeTableRecords($attributeGroups);
});

it('can render edit attributeGroup page.', function () {
    // Act & Assert
    get(AttributeGroupResource::getUrl('edit', [
        'record' => AttributeGroup::factory()->create(),
    ]))
        ->assertSuccessful();
});

it('can update attributeGroup model.', function () {
    // Arrange
    $attributeGroup = AttributeGroup::factory()->create();
    $newAttributeGroup = AttributeGroup::factory()->make();

    // Act & Assert
    livewire(AttributeGroupResource\Pages\EditAttributeGroup::class, [
        'record' => $attributeGroup->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newAttributeGroup->name,
            'order' => $newAttributeGroup->order,
            'ancestor_id' => $newAttributeGroup->ancestor_id,
            'label' => $newAttributeGroup->label,

        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($attributeGroup->refresh())
        ->name->toBe($newAttributeGroup->name)
        ->order->toBe($newAttributeGroup->order)
        ->ancestor_id->toBe($newAttributeGroup->ancestor_id)
        ->label->toBe($newAttributeGroup->label);

});

it('can create attributeGroup model.', function () {
    // Arrange
    $newAttributeGroup = AttributeGroup::factory()->make();

    livewire(AttributeGroupResource\Pages\CreateAttributeGroup::class)
        ->fillForm([
            'name' => $newAttributeGroup->name,
            'order' => $newAttributeGroup->order,
            'ancestor_id' => $newAttributeGroup->ancestor_id,
            'label' => $newAttributeGroup->label,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    //     Assert the database has the expected data
    $this->assertDatabaseHas(AttributeGroup::class, [
        'name' => $newAttributeGroup->name,
        'order' => $newAttributeGroup->order,
        'ancestor_id' => $newAttributeGroup->ancestor_id,
        'label' => $newAttributeGroup->label,
    ]);

});

it('cannot delete attributeGroup model.', function () {
    // Arrange
    $attributeGroup = AttributeGroup::factory()->create();

    // Act & Assert
    livewire(AttributeGroupResource\Pages\EditAttributeGroup::class, [
        'record' => $attributeGroup->getRouteKey(),
    ])
        ->assertActionDoesNotExist(DeleteAction::class);
});
