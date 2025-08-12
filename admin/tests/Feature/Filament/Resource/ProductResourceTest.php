<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Models\Image;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();  // You have login() helper, keep it here
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
    $file = UploadedFile::fake()->image('image.png', 500);

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
            'variety_counts' => $newProduct->variety_counts,
            'weight' => $newProduct->weight,
            'length' => $newProduct->length,
            'width' => $newProduct->width,
            'height' => $newProduct->height,
            'status' => $newProduct->status->value,
            'seen' => $newProduct->seen,
        ])
        ->set('data.images.0.path', [$file->getClientOriginalName()]) // you may adjust this based on your form
        ->set('data.images.0.is_featured', false)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh())
        ->heading->toBe($newProduct->heading)
        ->slug->toBe($newProduct->slug)
        ->price->toBe($newProduct->price)
        ->content->toBe($newProduct->content ? $newProduct->content : null)
        ->title->toBe($newProduct->title)
        ->description->toBe($newProduct->description)
        ->no_index->toBe($newProduct->no_index)
        ->canonical->toBe($newProduct->canonical)
        ->attribute_group_id->toBe($newProduct->attribute_group_id)
        ->category_id->toBe($newProduct->category_id)
        ->brand_id->toBe($newProduct->brand_id)
        ->minimum->toBe($newProduct->minimum)
        ->maximum->toBe($newProduct->maximum)
        ->step->toBe($newProduct->step)
        ->profit_percent->toBe($newProduct->profit_percent)
        ->has_stock->toBe($newProduct->has_stock)
        ->variety_counts->toBe($newProduct->variety_counts)
        ->weight->toBe($newProduct->weight)
        ->length->toBe($newProduct->length)
        ->width->toBe($newProduct->width)
        ->height->toBe($newProduct->height)
        ->status->toBe($newProduct->status)
        ->seen->toBe($newProduct->seen);

    $product = Product::query()->where('slug', $newProduct->slug)->first();

    $this->assertDatabaseHas(Image::class, [
        'path' => $file->getClientOriginalName(),
        'imageable_id' => $product->id,
        'imageable_type' => Product::class,
    ]);
});

it('can create product model.', function () {
    $newProduct = Product::factory()->make();
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
            'variety_counts' => $newProduct->variety_counts,
            'weight' => $newProduct->weight,
            'length' => $newProduct->length,
            'width' => $newProduct->width,
            'height' => $newProduct->height,
            'status' => $newProduct->status->value,
            'seen' => $newProduct->seen,
        ])
        ->set('data.images.0.path', [$file->getClientOriginalName()])
        ->set('data.images.0.is_featured', false)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'heading' => $newProduct->heading,
        'slug' => $newProduct->slug,
        'price' => $newProduct->price,
        'content' => $newProduct->content ? $newProduct->content : null,
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
        'variety_counts' => $newProduct->variety_counts,
        'weight' => $newProduct->weight,
        'length' => $newProduct->length,
        'width' => $newProduct->width,
        'height' => $newProduct->height,
        'status' => $newProduct->status->value,
        'seen' => $newProduct->seen,
    ]);

    $product = Product::query()->where('slug', $newProduct->slug)->first();

    $this->assertDatabaseHas(Image::class, [
        'path' => $file->getClientOriginalName(),
        'imageable_id' => $product->id,
        'imageable_type' => Product::class,
    ]);
});

it('can delete product model.', function () {
    $product = Product::factory()->create();

    livewire(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])->callAction(DeleteAction::class);

    $this->assertModelMissing($product);
});
