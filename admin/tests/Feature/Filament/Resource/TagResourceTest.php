<?php

declare(strict_types=1);

use App\Filament\Resources\TagResource;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Filament\Resources\TagResource\Pages\EditTag;
use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Tag;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the tag resource.', function () {
    get(TagResource::getUrl())->assertOk();
});

it('can list tags in the table.', function () {
    $tags = Tag::factory()->count(5)->create();

    livewire(ListTags::class)
        ->assertCanSeeTableRecords($tags);
});

it('can render edit tag page.', function () {
    $tag = Tag::factory()->create();

    get(TagResource::getUrl('edit', ['record' => $tag]))->assertOk();
});

it('can create a tag with a category and multiple attributes.', function () {
    $category = Category::factory()->create();
    $attributes = Attribute::factory()->count(2)->create();

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'تجهیزات گیمینگ',
            'slug' => 'gaming-gear',
            'category_id' => $category->id,
            'attributes' => $attributes->pluck('id')->all(),
            'no_index' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tag = Tag::query()->where('slug', 'gaming-gear')->firstOrFail();
    expect($tag->category_id)->toBe($category->id)
        ->and($tag->attributes)->toHaveCount(2);
});

it('can create an attribute-only tag (no category).', function () {
    $attribute = Attribute::factory()->create();

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'محصولات قرمز',
            'slug' => 'red-products',
            'attributes' => [$attribute->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tag = Tag::query()->where('slug', 'red-products')->firstOrFail();
    expect($tag->category_id)->toBeNull()
        ->and($tag->attributes)->toHaveCount(1);
});

it('requires at least one of category or attributes.', function () {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'بدون هیچ‌کدام',
            'slug' => 'neither',
            'category_id' => null,
            'attributes' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['category_id', 'attributes']);
});

it('requires a unique slug.', function () {
    Tag::factory()->create(['slug' => 'gaming-gear']);
    $category = Category::factory()->create();

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'دوباره',
            'slug' => 'gaming-gear',
            'category_id' => $category->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('can delete a tag.', function () {
    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($tag);
});
