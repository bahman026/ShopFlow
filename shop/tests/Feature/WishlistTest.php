<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Inertia\Testing\AssertableInertia;

it('adds a product to the wishlist', function (): void {
    $user = User::factory()->create();
    $product = makeProduct();

    $this->actingAs($user)
        ->post('/products/'.$product->id.'/wishlist')
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('wishlists', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

it('removes a product from the wishlist when toggled again', function (): void {
    $user = User::factory()->create();
    $product = makeProduct();
    Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)
        ->post('/products/'.$product->id.'/wishlist')
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

it('redirects guests to login when toggling the wishlist', function (): void {
    $product = makeProduct();

    $this->post('/products/'.$product->id.'/wishlist')->assertRedirect('/login');

    $this->assertDatabaseMissing('wishlists', ['product_id' => $product->id]);
});

it('flags the product page as wishlisted for a user who saved it', function (): void {
    $user = User::factory()->create();
    $product = makeProduct();
    Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)
        ->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('isWishlisted', true)
        );
});

it('flags the product page as not wishlisted for a guest or a user who has not saved it', function (): void {
    $product = makeProduct();

    $this->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('isWishlisted', false)
        );

    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/products/'.$product->slug)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('isWishlisted', false)
        );
});

it('redirects guests away from the wishlist list', function (): void {
    $this->get('/account/wishlist')->assertRedirect('/login');
});

it('shows an empty state when the user has no wishlist items', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/account/wishlist')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Wishlist/Index')
            ->has('products.data', 0)
        );
});

it('lists only the logged-in user\'s own wishlist items, newest first', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $first = makeProduct();
    Wishlist::create(['user_id' => $user->id, 'product_id' => $first->id]);

    $second = Product::create([
        'heading' => 'کالای دوم',
        'slug' => 'second-product',
        'price' => 500000,
        'category_id' => $first->category_id,
        'status' => $first->status,
    ]);
    Wishlist::create(['user_id' => $user->id, 'product_id' => $second->id]);

    $otherProduct = Product::create([
        'heading' => 'کالای دیگری',
        'slug' => 'other-user-product',
        'price' => 300000,
        'category_id' => $first->category_id,
        'status' => $first->status,
    ]);
    Wishlist::create(['user_id' => $other->id, 'product_id' => $otherProduct->id]);

    $this->actingAs($user)
        ->get('/account/wishlist')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Wishlist/Index')
            ->has('products.data', 2)
            ->where('products.data.0.id', $second->id)
            ->where('products.data.1.id', $first->id)
        );
});
