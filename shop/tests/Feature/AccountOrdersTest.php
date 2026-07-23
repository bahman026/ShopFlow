<?php

declare(strict_types=1);

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

function makeOrder(User $user, array $overrides = []): Order
{
    $variety = makeVariety(['price' => 100000]);
    $address = makeCheckoutAddress($user);

    $order = Order::create(array_merge([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'status' => OrderStatusEnum::PAID,
        'coupon_discount' => 0,
        'discount' => 0,
        'shipping_cost' => 20000,
        'total_products_price' => 200000,
        'tax' => 0,
        'total_price' => 220000,
        'src' => OrderSrcEnum::WEB,
    ], $overrides));

    $order->orderVarieties()->create([
        'product_id' => $variety->product_id,
        'variety_id' => $variety->id,
        'quantity' => 2,
        'price' => 100000,
        'discount' => 0,
        'coupon_discount' => 0,
        'final_price' => 200000,
    ]);

    return $order;
}

it('redirects guests away from order history', function (): void {
    $this->get('/account/orders')->assertRedirect('/login');
});

it('shows an empty state when the user has no orders', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/account/orders')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Orders/Index')
            ->has('orders.data', 0)
        );
});

it('lists only the logged-in user\'s own orders, newest first', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $first = makeOrder($user);
    $second = makeOrder($user);
    makeOrder($other);

    $this->actingAs($user)
        ->get('/account/orders')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Orders/Index')
            ->has('orders.data', 2)
            ->where('orders.data.0.id', $second->id)
            ->where('orders.data.1.id', $first->id)
            ->where('orders.data.0.statusLabel', OrderStatusEnum::PAID->label())
            ->where('orders.data.0.totalPrice', 220000)
            ->where('orders.data.0.itemCount', 2)
            ->where('orders.data.0.url', '/account/orders/'.$second->id)
        );
});

it('paginates the order history', function (): void {
    $user = User::factory()->create();

    for ($i = 0; $i < 11; $i++) {
        makeOrder($user);
    }

    $this->actingAs($user)
        ->get('/account/orders')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('orders.data', 10)
            ->where('orders.meta.currentPage', 1)
            ->where('orders.meta.lastPage', 2)
            ->where('orders.meta.total', 11)
        );

    $this->actingAs($user)
        ->get('/account/orders?page=2')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('orders.data', 1)
        );
});

it('shows a single order with its line items, address and totals', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user);

    $this->actingAs($user)
        ->get('/account/orders/'.$order->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Orders/Show')
            ->where('order.id', $order->id)
            ->where('order.totalPrice', 220000)
            ->has('order.lines', 1)
            ->where('order.address.name', 'منزل')
        );
});

it('forbids viewing another user\'s order', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = makeOrder($owner);

    $this->actingAs($other)
        ->get('/account/orders/'.$order->id)
        ->assertForbidden();
});

it('shows a printable receipt with its line items, address and totals', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user);

    $this->actingAs($user)
        ->get('/account/orders/'.$order->id.'/receipt')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Orders/Receipt')
            ->where('order.id', $order->id)
            ->where('order.trackingCode', $order->tracking_code)
            ->where('order.totalPrice', 220000)
            ->has('order.lines', 1)
            ->where('order.address.name', 'منزل')
        );
});

it('forbids downloading another user\'s receipt', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = makeOrder($owner);

    $this->actingAs($other)
        ->get('/account/orders/'.$order->id.'/receipt')
        ->assertForbidden();
});

it('redirects guests away from the receipt page', function (): void {
    $order = makeOrder(User::factory()->create());

    $this->get('/account/orders/'.$order->id.'/receipt')->assertRedirect('/login');
});

it('redirects guests away from the returns list', function (): void {
    $this->get('/account/returns')->assertRedirect('/login');
});

it('shows an empty state on the returns list when the user has no returned orders', function (): void {
    $user = User::factory()->create();
    makeOrder($user, ['status' => OrderStatusEnum::PAID]);

    $this->actingAs($user)
        ->get('/account/returns')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Orders/Index')
            ->where('title', 'مرجوعی‌های من')
            ->has('orders.data', 0)
        );
});

it('lists only the logged-in user\'s own returned orders on the returns page', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $returned = makeOrder($user, ['status' => OrderStatusEnum::RETURNED]);
    makeOrder($user, ['status' => OrderStatusEnum::PAID]);
    makeOrder($user, ['status' => OrderStatusEnum::CANCELED]);
    makeOrder($other, ['status' => OrderStatusEnum::RETURNED]);

    $this->actingAs($user)
        ->get('/account/returns')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Orders/Index')
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $returned->id)
            ->where('orders.data.0.statusLabel', OrderStatusEnum::RETURNED->label())
            ->where('orders.data.0.url', '/account/orders/'.$returned->id)
        );
});
