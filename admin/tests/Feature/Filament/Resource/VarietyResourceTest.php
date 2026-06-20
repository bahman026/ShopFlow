<?php

declare(strict_types=1);

use App\Filament\Resources\VarietyResource;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\Variety;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the variety resource.', function () {
    get(VarietyResource::getUrl())->assertOk();
});

it('can list varieties in the table.', function () {
    $product = Product::factory()->create();
    $varieties = Variety::factory()->count(5)->for($product)->create();

    livewire(VarietyResource\Pages\ListVarieties::class)
        ->assertCanSeeTableRecords($varieties);
});

it('can render edit variety page.', function () {
    $variety = Variety::factory()->create();

    get(VarietyResource::getUrl('edit', [
        'record' => $variety,
    ]))->assertOk();
});

it('can update variety model.', function () {
    $variety = Variety::factory()->create();
    $newVariety = Variety::factory()->make();
    $product = Product::factory()->create();

    livewire(VarietyResource\Pages\EditVariety::class, [
        'record' => $variety->getRouteKey(),
    ])
        ->fillForm([
            'product_id' => $product->id,
            'price' => $newVariety->price,
            'sale_price' => $newVariety->sale_price,
            'inventory' => $newVariety->inventory,
            'has_stock' => $newVariety->has_stock,
            'status' => $newVariety->status->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($variety->refresh())
        ->product_id->toBe($product->id)
        ->price->toBe($newVariety->price)
        ->sale_price->toBe($newVariety->sale_price)
        ->inventory->toBe($newVariety->inventory)
        ->has_stock->toBe($newVariety->has_stock)
        ->status->toBe($newVariety->status);
});

it('can create variety model.', function () {
    $product = Product::factory()->create();
    $newVariety = Variety::factory()->make(['product_id' => $product->id]);

    livewire(VarietyResource\Pages\CreateVariety::class)
        ->fillForm([
            'product_id' => $product->id,
            'price' => $newVariety->price,
            'sale_price' => $newVariety->sale_price,
            'inventory' => $newVariety->inventory,
            'has_stock' => $newVariety->has_stock,
            'status' => $newVariety->status->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Variety::class, [
        'product_id' => $product->id,
        'price' => $newVariety->price,
        'sale_price' => $newVariety->sale_price,
        'inventory' => $newVariety->inventory,
        'has_stock' => (int) $newVariety->has_stock,
        'status' => $newVariety->status->value,
    ]);
});

it('can delete variety model.', function () {
    $variety = Variety::factory()->create();

    livewire(VarietyResource\Pages\EditVariety::class, [
        'record' => $variety->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($variety);
});

it('auto-fills attribute_value and color from attribute when attribute_id is set.', function () {
    $attribute = Attribute::factory()->create(['value' => 'Red', 'color' => '#ff0000']);
    $variety = Variety::factory()->withAttribute($attribute)->create();

    expect($variety->refresh())
        ->attribute_value->toBe('Red')
        ->color->toBe('#ff0000');
});

it('auto-syncs variety_counts on product when a variety is created.', function () {
    $product = Product::factory()->create();

    expect($product->refresh())->variety_counts->toBe(0);

    Variety::factory()->for($product)->create();

    expect($product->refresh())->variety_counts->toBe(1);
});

it('auto-syncs variety_counts on product when a variety is deleted.', function () {
    $product = Product::factory()->create();
    $variety = Variety::factory()->for($product)->create();

    expect($product->refresh())->variety_counts->toBe(1);

    $variety->delete();

    expect($product->refresh())->variety_counts->toBe(0);
});

it('can attach additional attributes to a variety via the resource.', function () {
    $variety = Variety::factory()->create();
    $attribute = Attribute::factory()->create();

    livewire(VarietyResource\Pages\EditVariety::class, [
        'record' => $variety->getRouteKey(),
    ])
        ->fillForm([
            'attributes' => [$attribute->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($variety->refresh()->attributes)->toHaveCount(1)
        ->first()->id->toBe($attribute->id);
});

it('cascade-deletes variety_attribute pivot rows when variety is deleted.', function () {
    $variety = Variety::factory()->create();
    $attribute = Attribute::factory()->create();
    $variety->attributes()->attach($attribute);

    $this->assertDatabaseHas('attribute_variety', [
        'variety_id' => $variety->id,
        'attribute_id' => $attribute->id,
    ]);

    $variety->delete();

    $this->assertDatabaseMissing('attribute_variety', [
        'variety_id' => $variety->id,
    ]);
});

it('auto-syncs variety_counts on product when a variety is deleted via the resource.', function () {
    $product = Product::factory()->create();
    $variety = Variety::factory()->for($product)->create();

    expect($product->refresh())->variety_counts->toBe(1);

    livewire(VarietyResource\Pages\EditVariety::class, [
        'record' => $variety->getRouteKey(),
    ])->callAction(DeleteAction::class);

    $this->assertModelMissing($variety);
    expect($product->refresh())->variety_counts->toBe(0);
});
