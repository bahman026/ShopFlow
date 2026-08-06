<?php

declare(strict_types=1);

use App\Enums\CategoryStatusEnum;
use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Models\Variety;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

/**
 * A published product at $price with one variety, filed under $category.
 */
function couponVariety(int $price = 100000, ?Category $category = null): Variety
{
    $category ??= couponCategory('cat');

    $product = Product::create([
        'heading' => 'کالای تست '.uniqid(),
        'slug' => 'test-'.uniqid(),
        'price' => $price,
        'category_id' => $category->id,
        'status' => ProductStatusEnum::PUBLISHED,
    ]);

    return Variety::create([
        'product_id' => $product->id,
        'price' => $price,
        'inventory' => 5,
        'has_stock' => true,
        'status' => VarietyStatusEnum::PUBLISHED,
    ]);
}

function couponCategory(string $slug, ?int $parentId = null): Category
{
    return Category::create([
        'heading' => 'دسته '.$slug,
        'slug' => $slug.'-'.uniqid(),
        'status' => CategoryStatusEnum::ACTIVE,
        'parent_id' => $parentId,
    ]);
}

/**
 * `coupons` is admin-owned and the shop model is read-only, so seed rows with
 * forceFill rather than mass assignment.
 *
 * @param  array<string, mixed>  $overrides
 */
function couponFor(array $overrides = []): Coupon
{
    $coupon = new Coupon;
    $coupon->forceFill(array_merge([
        'name' => 'کد تست',
        'code' => 'SAVE'.strtoupper(substr(uniqid(), -6)),
        'amount' => 20000,
        'is_percent' => false,
        'shipping' => false,
        'status' => CouponStatusEnum::ACTIVE,
        'is_for' => CouponForEnum::EVERYONE,
        'total_used' => 0,
    ], $overrides));
    $coupon->save();

    return $coupon;
}

function couponUser(): User
{
    return User::create([
        'first_name' => 'مشتری',
        'last_name' => 'نمونه',
        'email' => 'coupon'.uniqid().'@example.com',
        'mobile' => '0912'.random_int(1000000, 9999999),
        'password' => Hash::make('secret-password'),
        'status' => UserStatusEnum::ACTIVE,
    ]);
}

/**
 * A logged-in customer. Carts are keyed by user id for them, which keeps the
 * tests independent of how a guest session id is established.
 */
function couponBuyer(): User
{
    $user = couponUser();
    test()->actingAs($user);

    return $user;
}

function cartWith(User $user, Variety $variety, int $count = 1): Cart
{
    return Cart::create([
        'user_id' => $user->id,
        'variety_id' => $variety->id,
        'count' => $count,
    ]);
}

it('previews a fixed-amount coupon on the cart', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety(100000));

    $coupon = couponFor(['amount' => 20000]);

    $this->post('/cart/coupon', ['code' => $coupon->code])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('coupon.code', $coupon->code)
        ->where('coupon.discount', 20000)
        ->where('summary.couponDiscount', 20000)
        ->where('summary.payable', 80000)
        ->etc());
});

it('previews a percentage coupon and honours its cap', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety(100000), 2);

    $coupon = couponFor(['amount' => 25, 'is_percent' => true, 'max_discount' => 30000]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();

    // 25% of 200,000 is 50,000, capped at 30,000.
    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('summary.couponDiscount', 30000)
        ->where('summary.payable', 170000)
        ->etc());
});

it('matches a coupon code case-insensitively', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());
    $coupon = couponFor(['code' => 'WELCOME10']);

    $this->post('/cart/coupon', ['code' => 'welcome10'])->assertSessionHasNoErrors();

    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('coupon.code', $coupon->code)
        ->etc());
});

it('rejects an unknown code', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());

    $this->post('/cart/coupon', ['code' => 'NOPE'])->assertSessionHasErrors('code');
});

it('rejects an expired coupon', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());
    $coupon = couponFor(['expired_at' => now()->subDay()]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasErrors('code');
});

it('rejects a coupon that has not started yet', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());
    $coupon = couponFor(['started_at' => now()->addDay()]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasErrors('code');
});

it('rejects an inactive coupon', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());
    $coupon = couponFor(['status' => CouponStatusEnum::CANCELED]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasErrors('code');
});

it('rejects a coupon whose uses are exhausted', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());
    $coupon = couponFor(['total_uses' => 5, 'total_used' => 5]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasErrors('code');
});

it('rejects a coupon below its minimum cart amount', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety(50000));
    $coupon = couponFor(['min_price' => 100000]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasErrors('code');
});

it('rejects a users-only coupon for a guest but accepts it for a customer', function (): void {
    $variety = couponVariety();
    $coupon = couponFor(['is_for' => CouponForEnum::USERS]);

    // A first request settles the guest session the cart is keyed by.
    $this->get('/cart');
    Cart::create([
        'session_id' => session()->getId(),
        'variety_id' => $variety->id,
        'count' => 1,
    ]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasErrors('code');

    $buyer = couponBuyer();
    cartWith($buyer, $variety);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();
});

it('rejects a coupon issued to a different customer', function (): void {
    $owner = couponUser();
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());

    $coupon = couponFor(['user_id' => $owner->id]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasErrors('code');
});

it('only discounts the lines a product-scoped coupon covers', function (): void {
    $buyer = couponBuyer();
    $covered = couponVariety(100000);
    cartWith($buyer, $covered);
    cartWith($buyer, couponVariety(60000));

    $coupon = couponFor(['amount' => 50, 'is_percent' => true]);
    $coupon->products()->attach($covered->product_id);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();

    // 50% of the covered line only (100,000), not of the 160,000 cart.
    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('summary.couponDiscount', 50000)
        ->where('summary.payable', 110000)
        ->etc());
});

it('covers sub-category products with a parent-category coupon', function (): void {
    $buyer = couponBuyer();
    $parent = couponCategory('parent');
    $child = couponCategory('child', $parent->id);

    cartWith($buyer, couponVariety(100000, $child));

    $coupon = couponFor(['amount' => 10000]);
    $coupon->categories()->attach($parent->id);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();

    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('summary.couponDiscount', 10000)
        ->etc());
});

it('rejects a scoped coupon that covers nothing in the cart', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());

    $coupon = couponFor();
    $coupon->products()->attach(couponVariety()->product_id);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasErrors('code');
});

it('never discounts more than the cart is worth', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety(30000));
    $coupon = couponFor(['amount' => 500000]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();

    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('summary.couponDiscount', 30000)
        ->where('summary.payable', 0)
        ->etc());
});

it('keeps a free-shipping coupon that discounts nothing', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());
    $coupon = couponFor(['amount' => 0, 'shipping' => true]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();

    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('coupon.freeShipping', true)
        ->where('summary.couponDiscount', 0)
        ->etc());
});

it('removes an applied coupon', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety());
    $coupon = couponFor();

    $this->post('/cart/coupon', ['code' => $coupon->code]);
    $this->delete('/cart/coupon')->assertRedirect();

    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('coupon', null)
        ->where('summary.couponDiscount', 0)
        ->etc());
});

it('drops a stored coupon that stopped being valid and explains why', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety(100000));
    $coupon = couponFor(['min_price' => 80000]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();

    // The cart shrinks below the coupon's minimum after it was applied.
    Cart::query()->where('user_id', $buyer->id)->delete();
    cartWith($buyer, couponVariety(50000));

    $this->get('/cart')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('coupon', null)
        ->where('couponError', trans('messages.cart.coupon.min_price'))
        ->where('summary.couponDiscount', 0)
        ->etc());
});

it('leaves the checkout totals untouched while a coupon is only previewed', function (): void {
    $buyer = couponBuyer();
    cartWith($buyer, couponVariety(100000));
    $coupon = couponFor(['amount' => 20000]);

    $this->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();

    $this->get('/checkout')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->where('summary.couponDiscount', 0)
        ->where('summary.payable', 100000)
        ->etc());
});
