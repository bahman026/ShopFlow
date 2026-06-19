<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeGroupCategory;
use App\Models\Image;
use App\Models\Product;
use App\Models\Variety;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the product resource.', function () {
    get(ProductResource::getUrl())->assertOk();
});

it('can list products in the table.', function () {
    $products = Product::factory()->count(5)->create();

    livewire(ProductResource\Pages\ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

it('can render edit product page.', function () {
    get(ProductResource::getUrl('edit', [
        'record' => Product::factory()->create(),
    ]))->assertSuccessful();
});

it('can update product model.', function () {
    $product = Product::factory()->create();
    $newProduct = Product::factory()->make();

    livewire(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->fillForm([
            'heading' => $newProduct->heading,
            'slug' => $newProduct->slug,
            'price' => $newProduct->price,
            'content' => $newProduct->content,
            'title' => $newProduct->title,
            'description' => $newProduct->description,
            'no_index' => $newProduct->no_index,
            'canonical' => $newProduct->canonical,
            'attribute_group_id' => $newProduct->attribute_group_id,
            'category_id' => $newProduct->category_id,
            'brand_id' => $newProduct->brand_id,
            'minimum' => $newProduct->minimum,
            'maximum' => $newProduct->maximum,
            'step' => $newProduct->step,
            'profit_percent' => $newProduct->profit_percent,
            'has_stock' => $newProduct->has_stock,
            'weight' => $newProduct->weight,
            'length' => $newProduct->length,
            'width' => $newProduct->width,
            'height' => $newProduct->height,
            'status' => $newProduct->status->value,
            'seen' => $newProduct->seen,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh())
        ->heading->toBe($newProduct->heading)
        ->price->toBe($newProduct->price)
        ->title->toBe($newProduct->title)
        ->no_index->toBe($newProduct->no_index)
        ->category_id->toBe($newProduct->category_id)
        ->brand_id->toBe($newProduct->brand_id)
        ->status->toBe($newProduct->status)
        ->seen->toBe($newProduct->seen);
});

it('can create product model.', function () {
    $newProduct = Product::factory()->make();
    $variety = Variety::factory()->make();
    $file = UploadedFile::fake()->image('image.png', 500);

    livewire(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'heading' => $newProduct->heading,
            'slug' => $newProduct->slug,
            'price' => $newProduct->price,
            'content' => $newProduct->content,
            'title' => $newProduct->title,
            'description' => $newProduct->description,
            'no_index' => $newProduct->no_index,
            'canonical' => $newProduct->canonical,
            'attribute_group_id' => $newProduct->attribute_group_id,
            'category_id' => $newProduct->category_id,
            'brand_id' => $newProduct->brand_id,
            'minimum' => $newProduct->minimum,
            'maximum' => $newProduct->maximum,
            'step' => $newProduct->step,
            'profit_percent' => $newProduct->profit_percent,
            'has_stock' => $newProduct->has_stock,
            'weight' => $newProduct->weight,
            'length' => $newProduct->length,
            'width' => $newProduct->width,
            'height' => $newProduct->height,
            'status' => $newProduct->status->value,
            'seen' => $newProduct->seen,
        ])
        ->set('data.images', [
            [
                'path' => [$file->getClientOriginalName()],
                'is_featured' => true,
            ],
        ])
        ->set('data.varieties', [
            [
                'attribute_value' => $variety->attribute_value,
                'color' => $variety->color,
                'price' => $variety->price,
                'sale_price' => $variety->sale_price,
                'inventory' => $variety->inventory,
                'has_stock' => $variety->has_stock,
                'status' => $variety->status,
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'heading' => $newProduct->heading,
        'slug' => $newProduct->slug,
        'status' => $newProduct->status->value,
    ]);

    $product = Product::query()->where('slug', $newProduct->slug)->first();

    $this->assertDatabaseHas(Image::class, [
        'path' => $file->getClientOriginalName(),
        'imageable_id' => $product->id,
        'imageable_type' => Product::class,
        'is_featured' => true,
    ]);

    $this->assertDatabaseHas(Variety::class, [
        'product_id' => $product->id,
        'attribute_value' => $variety->attribute_value,
        'price' => $variety->price,
        'inventory' => $variety->inventory,
    ]);
});

it('can delete product model.', function () {
    $product = Product::factory()->create();

    livewire(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])->callAction(DeleteAction::class);

    $this->assertModelMissing($product);
});

it('syncs attributes to the pivot table when saving a product.', function () {
    $product = Product::factory()->create();
    $attribute = Attribute::factory()->create();

    livewire(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->set('data.attributes', [
            [
                'attribute_id' => $attribute->id,
                'pivot' => ['is_highlight' => true],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('product_attribute', [
        'product_id' => $product->id,
        'attribute_id' => $attribute->id,
        'is_highlight' => true,
    ]);
});

it('blocks saving when required attribute groups are missing.', function () {
    $attributeGroup = AttributeGroup::factory()->create();
    $category = AttributeGroupCategory::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
        'required' => true,
    ]);
    $newProduct = Product::factory()->make(['category_id' => $category->category_id]);

    livewire(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'heading' => $newProduct->heading,
            'slug' => $newProduct->slug,
            'price' => $newProduct->price,
            'content' => $newProduct->content,
            'title' => $newProduct->title,
            'description' => $newProduct->description,
            'no_index' => $newProduct->no_index,
            'attribute_group_id' => $newProduct->attribute_group_id,
            'category_id' => $category->category_id,
            'brand_id' => $newProduct->brand_id,
            'minimum' => $newProduct->minimum,
            'step' => $newProduct->step,
            'profit_percent' => $newProduct->profit_percent,
            'has_stock' => $newProduct->has_stock,
            'status' => $newProduct->status->value,
            'seen' => $newProduct->seen,
        ])
        ->call('create');

    $this->assertDatabaseMissing(Product::class, ['heading' => $newProduct->heading]);
});

it('auto-syncs variety_counts on product when a variety is deleted.', function () {
    $product = Product::factory()->create();
    $variety = Variety::factory()->for($product)->create();

    expect($product->refresh())->variety_counts->toBe(1);

    $variety->delete();

    expect($product->refresh())->variety_counts->toBe(0);
});
