<?php

declare(strict_types=1);

use App\Enums\OrderStatusEnum;
use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

const RETRY_ZARINPAL_AUTHORITY = 'B00000000000000000000000000000000000';

function makeOrderTransaction(Order $order, array $overrides = []): void
{
    $order->transactions()->create(array_merge([
        'user_id' => $order->user_id,
        'port' => TransactionPortEnum::ZARINPAL,
        'amount' => $order->total_price,
        'status' => TransactionStatusEnum::CANCELED,
        'transaction_id' => 'A'.uniqid(),
    ], $overrides));
}

it('offers retry for a canceled order the customer never actually paid for', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user, ['status' => OrderStatusEnum::CANCELED]);
    makeOrderTransaction($order, ['status' => TransactionStatusEnum::CANCELED, 'paid_at' => null]);

    $this->actingAs($user)
        ->get('/account/orders/'.$order->id)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('order.canRetryPayment', true)
        );
});

it('never offers retry for an oversold order that zarinpal already charged', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user, ['status' => OrderStatusEnum::CANCELED]);
    // CompleteCheckoutPayment::failPaidButOversold() keeps paid_at set even
    // though the order itself is CANCELED — money was genuinely captured.
    makeOrderTransaction($order, ['status' => TransactionStatusEnum::FAILED, 'paid_at' => now(), 'ref_id' => '123456']);

    $this->actingAs($user)
        ->get('/account/orders/'.$order->id)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('order.canRetryPayment', false)
        );
});

it('does not offer retry for a paid order', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user, ['status' => OrderStatusEnum::PAID]);

    $this->actingAs($user)
        ->get('/account/orders/'.$order->id)
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('order.canRetryPayment', false)
        );
});

it('pays a retryable order directly, without touching the cart, when stock is still sufficient', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user, ['status' => OrderStatusEnum::CANCELED]);
    makeOrderTransaction($order, ['paid_at' => null]);
    $trackingCode = $order->tracking_code;

    Http::fake([
        '*/pg/v4/payment/request.json' => Http::response([
            'data' => ['code' => 100, 'authority' => RETRY_ZARINPAL_AUTHORITY],
        ]),
    ]);

    $this->actingAs($user)
        ->post('/account/orders/'.$order->id.'/retry')
        ->assertRedirect('https://sandbox.zarinpal.com/pg/StartPay/'.RETRY_ZARINPAL_AUTHORITY);

    // Retry reuses the same order (reset to PENDING) instead of cloning a
    // new one, so a customer who cancels and retries repeatedly doesn't
    // accumulate a new order row per attempt.
    $order->refresh();
    expect($order->status)->toBe(OrderStatusEnum::PENDING);
    expect($order->tracking_code)->toBe($trackingCode);
    expect(Order::query()->count())->toBe(1);

    $transaction = Transaction::query()->where('order_id', $order->id)->latest('id')->firstOrFail();
    expect($transaction->transaction_id)->toBe(RETRY_ZARINPAL_AUTHORITY);
    expect(Transaction::query()->where('order_id', $order->id)->count())->toBe(2); // original + this retry

    expect(Cart::query()->where('user_id', $user->id)->count())->toBe(0);
});

it('allows retrying again after a retry attempt is itself canceled at the gateway', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user, ['status' => OrderStatusEnum::CANCELED]);
    makeOrderTransaction($order, ['paid_at' => null]);

    Http::fake([
        '*/pg/v4/payment/request.json' => Http::sequence()
            ->push(['data' => ['code' => 100, 'authority' => RETRY_ZARINPAL_AUTHORITY]])
            ->push(['data' => ['code' => 100, 'authority' => 'C00000000000000000000000000000000000']]),
    ]);

    $this->actingAs($user)->post('/account/orders/'.$order->id.'/retry');

    // Customer backs out at Zarinpal — same callback path as a normal
    // checkout cancel.
    $this->actingAs($user)->get('/checkout/callback?Authority='.RETRY_ZARINPAL_AUTHORITY.'&Status=NOK');

    expect($order->refresh()->status)->toBe(OrderStatusEnum::CANCELED);
    expect($order->isRetryable())->toBeTrue();
    expect(Order::query()->count())->toBe(1);

    // Retry again — still the same order, a third transaction row.
    $this->actingAs($user)
        ->post('/account/orders/'.$order->id.'/retry')
        ->assertRedirect('https://sandbox.zarinpal.com/pg/StartPay/C00000000000000000000000000000000000');

    expect($order->refresh()->status)->toBe(OrderStatusEnum::PENDING);
    expect(Order::query()->count())->toBe(1);
    expect(Transaction::query()->where('order_id', $order->id)->count())->toBe(3);
});

it('forbids retrying an order that is not retryable', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user, ['status' => OrderStatusEnum::PAID]);

    $this->actingAs($user)
        ->post('/account/orders/'.$order->id.'/retry')
        ->assertForbidden();
});

it('forbids retrying another user\'s order', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = makeOrder($owner, ['status' => OrderStatusEnum::CANCELED]);
    makeOrderTransaction($order, ['paid_at' => null]);

    $this->actingAs($other)
        ->post('/account/orders/'.$order->id.'/retry')
        ->assertForbidden();
});

it('does not proceed to payment when stock is no longer sufficient', function (): void {
    $user = User::factory()->create();
    $order = makeOrder($user, ['status' => OrderStatusEnum::CANCELED]);
    makeOrderTransaction($order, ['paid_at' => null]);
    $order->orderVarieties()->first()->variety->update(['inventory' => 0]);

    $this->actingAs($user)
        ->post('/account/orders/'.$order->id.'/retry')
        ->assertRedirect('/account/orders/'.$order->id)
        ->assertSessionHas('status');

    expect(Order::query()->count())->toBe(1);
    expect(Transaction::query()->count())->toBe(1); // only the original canceled-order transaction, no new one
});
