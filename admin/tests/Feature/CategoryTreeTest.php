<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Role;

// categories.parent_id used to be an unconstrained integer, so deleting a
// parent left its children pointing at a row that no longer existed — and the
// storefront, which walks parent_id, silently lost the whole subtree.

it('refuses at the database to delete a category that still has children', function () {
    $parent = Category::factory()->create();
    Category::factory()->create(['parent_id' => $parent->id]);

    expect(fn () => $parent->delete())->toThrow(QueryException::class);

    expect(Category::query()->whereKey($parent->id)->exists())->toBeTrue();
});

it('allows deleting a leaf category', function () {
    $leaf = Category::factory()->create();

    $leaf->delete();

    expect(Category::query()->whereKey($leaf->id)->exists())->toBeFalse();
});

it('cannot orphan a child by pointing it at a category that does not exist', function () {
    $category = Category::factory()->create();

    expect(fn () => $category->update(['parent_id' => 999999]))
        ->toThrow(QueryException::class);
});

it('hides the delete action for a category that still has children or products', function () {
    Role::findOrCreate(RolesEnum::SUPER_ADMIN->value);
    /** @var User $superAdmin */
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RolesEnum::SUPER_ADMIN->value);

    $leaf = Category::factory()->create();
    $withChild = Category::factory()->create();
    Category::factory()->create(['parent_id' => $withChild->id]);
    $withProduct = Category::factory()->create();
    Product::factory()->create(['category_id' => $withProduct->id]);

    expect($superAdmin->can('delete', $leaf))->toBeTrue()
        ->and($superAdmin->can('delete', $withChild))->toBeFalse()
        ->and($superAdmin->can('delete', $withProduct))->toBeFalse();
});

it('still keeps deletion to super-admins', function () {
    Role::findOrCreate(RolesEnum::ADMIN->value);
    /** @var User $admin */
    $admin = User::factory()->create();
    $admin->assignRole(RolesEnum::ADMIN->value);

    expect($admin->can('delete', Category::factory()->create()))->toBeFalse();
});
