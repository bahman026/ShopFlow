<?php

declare(strict_types=1);

use App\Filament\Resources\AttributeGroupCategoryResource;
use App\Models\AttributeGroupCategory;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the attributeGroupCategory resource.', function () {
    // Act & Assert
    get(AttributeGroupCategoryResource::getUrl())->assertOk();
});

it('can list attributeGroupCategories in the table.', function () {
    // Arrange
    $attributeGroupCategories = AttributeGroupCategory::factory()->count(5)->create();

    // Act & Assert
    livewire(AttributeGroupCategoryResource\Pages\ListAttributeGroupCategories::class)
        ->assertCanSeeTableRecords($attributeGroupCategories);
});

it('can render edit attributeGroupCategory page.', function () {
    // Act & Assert
    get(AttributeGroupCategoryResource::getUrl('edit', [
        'record' => AttributeGroupCategory::factory()->create(),
    ]))
        ->assertSuccessful();
});

it('can update attributeGroupCategory model.', function () {
    // Arrange
    $attributeGroupCategory = AttributeGroupCategory::factory()->create();
    $newAttributeGroupCategory = AttributeGroupCategory::factory()->make();

    // Act & Assert
    livewire(AttributeGroupCategoryResource\Pages\EditAttributeGroupCategory::class, [
        'record' => $attributeGroupCategory->getRouteKey(),
    ])
        ->fillForm([
            'category_id' => $newAttributeGroupCategory->category_id,
            'attribute_group_id' => $newAttributeGroupCategory->attribute_group_id,
            'as_filter' => $newAttributeGroupCategory->as_filter,
            'required' => $newAttributeGroupCategory->required,

        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($attributeGroupCategory->refresh())
        ->category_id->toBe($newAttributeGroupCategory->category_id)
        ->attribute_group_id->toBe($newAttributeGroupCategory->attribute_group_id)
        ->as_filter->toBe((int) $newAttributeGroupCategory->as_filter)
        ->required->toBe((int) $newAttributeGroupCategory->required);

});

it('can create attributeGroupCategory model.', function () {
    // Arrange
    $newAttributeGroupCategory = AttributeGroupCategory::factory()->make();

    livewire(AttributeGroupCategoryResource\Pages\CreateAttributeGroupCategory::class)
        ->fillForm([
            'category_id' => $newAttributeGroupCategory->category_id,
            'attribute_group_id' => $newAttributeGroupCategory->attribute_group_id,
            'as_filter' => $newAttributeGroupCategory->as_filter,
            'required' => $newAttributeGroupCategory->required,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    //     Assert the database has the expected data
    $this->assertDatabaseHas(AttributeGroupCategory::class, [
        'category_id' => $newAttributeGroupCategory->category_id,
        'attribute_group_id' => $newAttributeGroupCategory->attribute_group_id,
        'as_filter' => $newAttributeGroupCategory->as_filter,
        'required' => $newAttributeGroupCategory->required,
    ]);

});

it('cannot delete attributeGroupCategory model.', function () {
    // Arrange
    $attributeGroupCategory = AttributeGroupCategory::factory()->create();

    // Act & Assert
    livewire(AttributeGroupCategoryResource\Pages\EditAttributeGroupCategory::class, [
        'record' => $attributeGroupCategory->getRouteKey(),
    ])
        ->assertActionDoesNotExist(DeleteAction::class);
});
