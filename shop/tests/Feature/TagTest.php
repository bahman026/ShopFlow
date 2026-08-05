<?php

declare(strict_types=1);

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

/**
 * Create an attribute group filterable on the category and return two
 * attributes in it. Mirrors the setup CategoryPageTest uses.
 *
 * @return array{0: Attribute, 1: Attribute}
 */
function tagAttributes(Category $category): array
{
    $ancestorId = DB::table('ancestors')->insertGetId([
        'name' => 'نوع', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $groupId = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => $ancestorId, 'name' => 'نوع', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('attribute_group_category')->insert([
        'attribute_group_id' => $groupId,
        'category_id' => $category->id,
        'as_filter' => true,
        'required' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        Attribute::create(['attribute_group_id' => $groupId, 'value' => 'گیمینگ', 'color' => '#ff0000']),
        Attribute::create(['attribute_group_id' => $groupId, 'value' => 'اداری', 'color' => '#0000ff']),
    ];
}

it('lists only products that carry the tag\'s attribute', function (): void {
    $category = catCategory('gaming');
    [$gaming, $office] = tagAttributes($category);

    catProduct($category, attributeId: $gaming->id);
    catProduct($category, attributeId: $office->id);

    $tag = Tag::create([
        'name' => 'تجهیزات گیمینگ',
        'slug' => 'gaming-gear',
        'category_id' => $category->id,
        'no_index' => false,
    ]);
    $tag->attributes()->attach($gaming->id);

    $this->get('/tags/'.$tag->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Tags/Show')
            ->where('tag.name', 'تجهیزات گیمینگ')
            ->where('products.meta.total', 1) // only the gaming product, not the office one
            ->has('breadcrumbs', 3)
            ->where('breadcrumbs.0.heading', 'خانه')
            ->where('breadcrumbs.2.heading', 'تجهیزات گیمینگ')
        );
});

it('lists a category-only tag\'s whole category (no attribute filter)', function (): void {
    $category = catCategory('all-shoes');
    catProduct($category);
    catProduct($category);

    $tag = Tag::create([
        'name' => 'همه کفش‌ها',
        'slug' => 'all-shoes',
        'category_id' => $category->id,
    ]);

    $this->get('/tags/'.$tag->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 2)
        );
});

it('lists an attribute-only tag across categories (no category)', function (): void {
    $shoes = catCategory('cross-shoes');
    $bags = catCategory('cross-bags');
    [$gaming] = tagAttributes($shoes);

    catProduct($shoes, attributeId: $gaming->id);
    catProduct($bags, attributeId: $gaming->id);
    catProduct($bags); // no attribute → excluded

    $tag = Tag::create([
        'name' => 'محصولات گیمینگ',
        'slug' => 'gaming-all',
        'category_id' => null,
    ]);
    $tag->attributes()->attach($gaming->id);

    $this->get('/tags/'.$tag->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 2) // both categories' gaming products
            ->has('breadcrumbs', 2) // Home → Tag (no category crumb)
        );
});

it('applies multiple attributes across groups as AND', function (): void {
    $category = catCategory('multi');
    [$gaming] = tagAttributes($category);

    // A second group + attribute so the two are AND-ed across groups.
    $groupId = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => DB::table('ancestors')->insertGetId(['name' => 'برند', 'created_at' => now(), 'updated_at' => now()]),
        'name' => 'برند', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('attribute_group_category')->insert([
        'attribute_group_id' => $groupId, 'category_id' => $category->id,
        'as_filter' => true, 'required' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $premium = Attribute::create(['attribute_group_id' => $groupId, 'value' => 'پریمیوم']);

    $both = catProduct($category, attributeId: $gaming->id);
    $both->attributes()->attach($premium->id);
    catProduct($category, attributeId: $gaming->id); // gaming only, not premium

    $tag = Tag::create(['name' => 'گیمینگ پریمیوم', 'slug' => 'gaming-premium', 'category_id' => $category->id]);
    $tag->attributes()->attach([$gaming->id, $premium->id]);

    $this->get('/tags/'.$tag->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1) // only the product with BOTH attributes
        );
});

it('returns 404 for an unknown tag slug', function (): void {
    $this->get('/tags/does-not-exist')->assertNotFound();
});

it('honours the tag no_index flag for SEO', function (): void {
    $category = catCategory('summer');
    $tag = Tag::create([
        'name' => 'لوازم تابستانی',
        'slug' => 'summer-gear',
        'category_id' => $category->id,
        'no_index' => true,
    ]);

    $this->get('/tags/'.$tag->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tag.noIndex', true)
        );
});
