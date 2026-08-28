<?php

declare(strict_types=1);

use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const ZARINPAL_AUTHORITY = 'A00000000000000000000000000000000000';

function startFakeCheckoutPayment(): array
{
    $user = User::factory()->create();
    $variety = makeVariety(['inventory' => 5]);
    $address = makeCheckoutAddress($user);
    $method = makeNationwideMethod();

    test()->actingAs($user)->post('/cart', ['variety_id' => $variety->id, 'count' => 2]);
    test()->actingAs($user)->post('/checkout/shipping', [
        'address_id' => $address->id,
        'shipping_method_id' => $method->id,
    ]);

    Http::fake([
        '*/pg/v4/payment/request.json' => Http::response([
            'data' => ['code' => 100, 'authority' => ZARINPAL_AUTHORITY],
        ]),
        '*/pg/v4/payment/verify.json' => Http::response([
            'data' => ['code' => 100, 'ref_id' => 123456, 'card_pan' => '1234********5678'],
        ]),
    ]);

    test()->actingAs($user)
        ->post('/checkout/payment')
        ->assertRedirect('https://sandbox.zarinpal.com/pg/StartPay/'.ZARINPAL_AUTHORITY);

    return [$user, $variety];
}

it('creates a pending order + transaction and redirects to zarinpal', function (): void {
    [$user, $variety] = startFakeCheckoutPayment();

    $order = Order::query()->firstOrFail();
    expect($order->status)->toBe(OrderStatusEnum::PENDING);
    expect($order->user_id)->toBe($user->id);
    expect($order->total_price)->toBe(205000); // (2 * 80000) + 45000 shipping
    expect($order->tracking_code)->toMatch('/^[1-9]\d{9}$/');

    $transaction = Transaction::query()->firstOrFail();
    expect($transaction->status)->toBe(TransactionStatusEnum::PENDING);
    expect($transaction->transaction_id)->toBe(ZARINPAL_AUTHORITY);
    expect($transaction->amount)->toBe(205000);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
        && $request['amount'] === 2050000 // Toman -> Rial (x10)
    );
});

it('marks the order paid and decrements inventory on a successful callback', function (): void {
    [$user, $variety] = startFakeCheckoutPayment();
    $order = Order::query()->firstOrFail();

    $this->actingAs($user)
        ->get('/checkout/callback?Authority='.ZARINPAL_AUTHORITY.'&Status=OK')
        ->assertRedirect('/checkout/confirmation/'.$order->id)
        ->assertSessionMissing('checkout.address_id')
        ->assertSessionMissing('checkout.shipping_method_id');

    $order->refresh();
    $transaction = Transaction::query()->firstOrFail();

    expect($order->status)->toBe(OrderStatusEnum::PAID);
    expect($transaction->status)->toBe(TransactionStatusEnum::SUCCESS);
    expect($transaction->ref_id)->toBe('123456');
    expect($variety->refresh()->inventory)->toBe(3);
    expect(Cart::query()->where('user_id', $user->id)->count())->toBe(0);
});

it('cancels the order without touching inventory when the customer cancels at zarinpal', function (): void {
    [$user, $variety] = startFakeCheckoutPayment();
    $order = Order::query()->firstOrFail();

    $this->actingAs($user)
        ->get('/checkout/callback?Authority='.ZARINPAL_AUTHORITY.'&Status=NOK')
        ->assertRedirect('/checkout/payment');

    expect($order->refresh()->status)->toBe(OrderStatusEnum::CANCELED);
    expect(Transaction::query()->firstOrFail()->status)->toBe(TransactionStatusEnum::CANCELED);
    expect($variety->refresh()->inventory)->toBe(5);
});

it('does not re-verify or re-decrement on a repeated callback for an already-paid order', function (): void {
    [$user, $variety] = startFakeCheckoutPayment();
    $order = Order::query()->firstOrFail();

    $this->actingAs($user)->get('/checkout/callback?Authority='.ZARINPAL_AUTHORITY.'&Status=OK');
    expect($variety->refresh()->inventory)->toBe(3);

    $this->actingAs($user)
        ->get('/checkout/callback?Authority='.ZARINPAL_AUTHORITY.'&Status=OK')
        ->assertRedirect('/checkout/confirmation/'.$order->id);

    expect($variety->refresh()->inventory)->toBe(3);
    Http::assertSentCount(2); // one request.json + one verify.json, never a second verify
});

it('cancels the order if inventory ran out before payment confirmation, but keeps the ref_id for a manual refund', function (): void {
    [$user, $variety] = startFakeCheckoutPayment();
    $order = Order::query()->firstOrFail();

    // Simulate stock disappearing between order creation and payment
    // confirmation (the rare race ORDER.md accepts as unavoidable) — Zarinpal
    // still genuinely captured the money, so the transaction must keep proof
    // of that for a manual refund, not look like a plain failed payment.
    $variety->update(['inventory' => 0]);

    $this->actingAs($user)
        ->get('/checkout/callback?Authority='.ZARINPAL_AUTHORITY.'&Status=OK')
        ->assertRedirect('/checkout/payment');

    $transaction = Transaction::query()->firstOrFail();

    expect($order->refresh()->status)->toBe(OrderStatusEnum::CANCELED);
    expect($transaction->status)->toBe(TransactionStatusEnum::FAILED);
    expect($transaction->ref_id)->toBe('123456');
    expect($transaction->paid_at)->not->toBeNull();
    expect($transaction->result_message)->toContain('بازگشت وجه');
    expect($variety->refresh()->inventory)->toBe(0);
});

it('rejects checkout without charging when a cart item is already out of stock', function (): void {
    $user = User::factory()->create();
    $variety = makeVariety(['inventory' => 5]);
    $address = makeCheckoutAddress($user);
    $method = makeNationwideMethod();

    $this->actingAs($user)->post('/cart', ['variety_id' => $variety->id, 'count' => 2]);
    $this->actingAs($user)->post('/checkout/shipping', [
        'address_id' => $address->id,
        'shipping_method_id' => $method->id,
    ]);

    // Stock disappears entirely before the customer clicks "پرداخت".
    $variety->update(['inventory' => 0]);

    Http::fake([
        '*/pg/v4/payment/request.json' => Http::response(['data' => ['code' => 100, 'authority' => ZARINPAL_AUTHORITY]]),
    ]);

    $this->actingAs($user)
        ->post('/checkout/payment')
        ->assertRedirect('/cart')
        ->assertSessionHas('status');

    expect(Order::query()->count())->toBe(0);
    expect(Transaction::query()->count())->toBe(0);
    Http::assertNothingSent();
});

/**
 * A coupon applied on the cart has to survive all the way to the amount that
 * is actually charged — the cart promising a saving that checkout ignores is
 * worse than not offering the coupon at all.
 */
function couponCheckout(array $overrides = []): array
{
    $user = User::factory()->create();
    $variety = makeVariety(['inventory' => 5]);
    $address = makeCheckoutAddress($user);
    $method = makeNationwideMethod();

    // `coupons` is admin-owned and the shop model is read-only, so seed with
    // forceFill rather than mass assignment.
    $coupon = new Coupon;
    $coupon->forceFill(array_merge([
        'name' => 'کد تست',
        'code' => 'PAY'.strtoupper(substr(uniqid(), -6)),
        'amount' => 20000,
        'is_percent' => false,
        'shipping' => false,
        'status' => CouponStatusEnum::ACTIVE,
        'is_for' => CouponForEnum::EVERYONE,
        'total_used' => 0,
    ], $overrides));
    $coupon->save();

    test()->actingAs($user)->post('/cart', ['variety_id' => $variety->id, 'count' => 2]);
    test()->actingAs($user)->post('/cart/coupon', ['code' => $coupon->code])->assertSessionHasNoErrors();
    test()->actingAs($user)->post('/checkout/shipping', [
        'address_id' => $address->id,
        'shipping_method_id' => $method->id,
    ]);

    Http::fake([
        '*/pg/v4/payment/request.json' => Http::response(['data' => ['code' => 100, 'authority' => ZARINPAL_AUTHORITY]]),
        '*/pg/v4/payment/verify.json' => Http::response(['data' => ['code' => 100, 'ref_id' => 123456, 'card_pan' => '1234********5678']]),
    ]);

    return [$user, $coupon];
}

it('charges the discounted amount when a coupon is applied', function (): void {
    [$user, $coupon] = couponCheckout();

    test()->actingAs($user)->post('/checkout/payment')->assertRedirect();

    // 2 * 80000 = 160000, less the 20000 coupon, plus 45000 shipping.
    $order = Order::query()->firstOrFail();
    expect($order->total_price)->toBe(185000)
        ->and($order->coupon_id)->toBe($coupon->id)
        ->and((int) $order->coupon_discount)->toBe(20000);

    expect((int) Transaction::query()->firstOrFail()->amount)->toBe(185000);

    // And the gateway is asked for exactly that, in Rial.
    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'payment/request.json')
        && $request['amount'] === 1850000);
});

it('zeroes the shipping cost for a free-shipping coupon', function (): void {
    [$user] = couponCheckout(['amount' => 0, 'shipping' => true]);

    test()->actingAs($user)->post('/checkout/payment')->assertRedirect();

    $order = Order::query()->firstOrFail();
    expect((int) $order->shipping_cost)->toBe(0)
        ->and($order->total_price)->toBe(160000); // no shipping charged
});

it('counts the coupon use only once the payment succeeds', function (): void {
    [$user, $coupon] = couponCheckout();

    test()->actingAs($user)->post('/checkout/payment')->assertRedirect();

    // Still pending: an order that is never paid must not burn a use.
    expect($coupon->fresh()->total_used)->toBe(0);

    test()->actingAs($user)->get('/checkout/callback?Authority='.ZARINPAL_AUTHORITY.'&Status=OK')
        ->assertRedirect();

    expect($coupon->fresh()->total_used)->toBe(1)
        ->and(Order::query()->firstOrFail()->status)->toBe(OrderStatusEnum::PAID);
});

it('refuses to charge when the applied coupon has stopped being valid', function (): void {
    [$user, $coupon] = couponCheckout();

    // Expires between the cart and the payment click.
    $coupon->forceFill(['status' => CouponStatusEnum::CANCELED])->save();

    test()->actingAs($user)->post('/checkout/payment')->assertRedirect('/cart');

    // Nothing charged, nothing ordered — better than silently charging full price.
    expect(Order::query()->count())->toBe(0);
    Http::assertNothingSent();
});
