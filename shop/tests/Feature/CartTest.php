<?php

declare(strict_types=1);

use App\Actions\Cart\MergeGuestCart;
use App\Enums\CategoryStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\City;
use App\Models\Product;
use App\Models\Province;
use App\Models\ShippingCity;
use App\Models\ShippingLine;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Variety;
use Inertia\Testing\AssertableInertia;

function makeVariety(array $overrides = []): Variety
{
    $category = Category::create([
        'heading' => 'دسته تست',
        'slug' => 'cat-'.uniqid(),
        'status' => CategoryStatusEnum::ACTIVE,
    ]);

    $product = Product::create([
        'heading' => 'کالای تست '.uniqid(),
        'slug' => 'test-'.uniqid(),
        'price' => 100000,
        'category_id' => $category->id,
        'status' => ProductStatusEnum::PUBLISHED,
    ]);

    return Variety::create(array_merge([
        'product_id' => $product->id,
        'price' => 100000,
        'sale_price' => 80000,
        'inventory' => 5,
        'has_stock' => true,
        'status' => VarietyStatusEnum::PUBLISHED,
    ], $overrides));
}

function makeCheckoutAddress(User $user): Address
{
    $province = Province::create(['name' => 'تهران '.uniqid()]);
    $city = City::create(['name' => 'شهر', 'province_id' => $province->id]);

    return Address::create([
        'user_id' => $user->id,
        'name' => 'منزل',
        'phone' => '09121112233',
        'postal_code' => '1234567890',
        'address' => 'خیابان آزادی',
        'city_id' => $city->id,
        'prime' => true,
    ]);
}

function makeNationwideMethod(string $name = 'پست', int $amount = 45000): ShippingMethod
{
    $line = ShippingLine::create(['name' => $name, 'cost' => $amount]);
    $method = ShippingMethod::create([
        'shipping_line_id' => $line->id,
        'name' => $name,
        'status' => true,
    ]);
    ShippingCity::create([
        'shipping_method_id' => $method->id,
        'amount' => $amount,
        'sending_days' => '۲ تا ۴ روز کاری',
        'status' => true,
    ]);

    return $method;
}

it('adds a variety to the cart', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety();

    $this->actingAs($user)
        ->post('/cart', ['variety_id' => $variety->id, 'count' => 2])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->actingAs($user)
        ->get('/cart')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Cart/Index')
            ->has('lines', 1)
            ->where('lines.0.count', 2)
            ->where('lines.0.unitPrice', 80000)
            ->where('summary.payable', 160000)
            ->where('summary.discount', 40000)
        );
});

it('clamps the quantity to the available inventory', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety(['inventory' => 3]);

    $this->actingAs($user)->post('/cart', ['variety_id' => $variety->id, 'count' => 10]);

    $line = Cart::query()->firstOrFail();
    expect($line->count)->toBe(3);

    $this->actingAs($user)->patch('/cart/'.$line->id, ['count' => 99]);
    expect($line->refresh()->count)->toBe(3);
});

it('rejects adding an out-of-stock variety', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety(['inventory' => 0, 'has_stock' => false]);

    $this->actingAs($user)
        ->post('/cart', ['variety_id' => $variety->id])
        ->assertSessionHasErrors('variety_id');
});

it('removes a cart line', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety();
    $this->actingAs($user)->post('/cart', ['variety_id' => $variety->id]);
    $line = Cart::query()->firstOrFail();

    $this->actingAs($user)->delete('/cart/'.$line->id)->assertRedirect();

    expect(Cart::query()->count())->toBe(0);
});

it('forbids touching another user cart line', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $variety = makeVariety();

    $line = Cart::query()->create([
        'user_id' => $owner->id,
        'variety_id' => $variety->id,
        'count' => 1,
    ]);

    $this->actingAs($other)
        ->patch('/cart/'.$line->id, ['count' => 2])
        ->assertForbidden();
});

it('merges a guest cart into the user on login', function (): void {
    $variety = makeVariety();
    $user = User::factory()->create();

    Cart::query()->create([
        'session_id' => 'guest-session',
        'variety_id' => $variety->id,
        'count' => 2,
    ]);

    app(MergeGuestCart::class)($user, 'guest-session');

    $line = Cart::query()->where('user_id', $user->id)->firstOrFail();
    expect($line->count)->toBe(2);
    expect(Cart::query()->whereNull('user_id')->count())->toBe(0);
});

it('redirects guests away from checkout', function (): void {
    $this->get('/checkout')->assertRedirect('/login');
});

it('sends an empty cart back from checkout', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/checkout')
        ->assertRedirect('/cart');
});

it('shows the shipping step with no addresses so one can be added', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety();

    $this->actingAs($user)->post('/cart', ['variety_id' => $variety->id]);

    $this->actingAs($user)
        ->get('/checkout')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Checkout/Shipping')
            ->has('addresses', 0)
        );
});

it('lists shipping methods for the selected address', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety();
    makeCheckoutAddress($user);
    makeNationwideMethod();

    $this->actingAs($user)->post('/cart', ['variety_id' => $variety->id]);

    $this->actingAs($user)
        ->get('/checkout')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Checkout/Shipping')
            ->has('shippingMethods', 1)
            ->where('shippingMethods.0.name', 'پست')
            ->where('shippingMethods.0.cost', 45000)
        );
});

it('stores the shipping selection and shows it at payment', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety();
    $address = makeCheckoutAddress($user);
    $method = makeNationwideMethod();

    $this->actingAs($user)->post('/cart', ['variety_id' => $variety->id]);

    $this->actingAs($user)
        ->post('/checkout/shipping', ['address_id' => $address->id, 'shipping_method_id' => $method->id])
        ->assertRedirect('/checkout/payment');

    $this->actingAs($user)
        ->get('/checkout/payment')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Checkout/Payment')
            ->where('shippingMethod.name', 'پست')
            ->where('address.name', 'منزل')
        );
});

it('rejects a shipping method not available for the address', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety();
    $address = makeCheckoutAddress($user);
    makeNationwideMethod();

    $this->actingAs($user)->post('/cart', ['variety_id' => $variety->id]);

    $this->actingAs($user)
        ->post('/checkout/shipping', ['address_id' => $address->id, 'shipping_method_id' => 999999])
        ->assertSessionHasErrors('shipping_method_id');
});
