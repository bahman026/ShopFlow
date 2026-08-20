<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\DTOs\CartLineDTO;
use App\DTOs\CartSummaryDTO;
use App\DTOs\CouponDTO;
use App\DTOs\ShippingMethodDTO;
use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Models\Variety;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreatePendingOrder
{
    /**
     * Snapshot the cart into a PENDING order + its line items. Never touches
     * inventory (Strategy A, docs/ORDER.md) — that only happens on payment.
     *
     * The coupon is recorded at order level: `coupon_id` and the saving. Its
     * `total_used` is deliberately NOT incremented here — an order that is
     * never paid for must not burn a use (see DecrementInventoryAndMarkPaid).
     *
     * @param  Collection<int, CartLineDTO>  $lines
     */
    public function __invoke(User $user, Collection $lines, Address $address, ShippingMethodDTO $method, CartSummaryDTO $summary, ?CouponDTO $coupon = null): Order
    {
        return DB::transaction(function () use ($user, $lines, $address, $method, $summary, $coupon): Order {
            $varieties = Variety::query()
                ->whereIn('id', $lines->pluck('varietyId'))
                ->get()
                ->keyBy('id');

            // A free-shipping coupon zeroes the delivery charge; pay-on-delivery
            // is already settled with the courier, not online.
            $shippingCost = $method->payOnDelivery || ($coupon !== null && $coupon->freeShipping)
                ? 0
                : ($method->cost ?? 0);

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => OrderStatusEnum::PENDING,
                'coupon_id' => $coupon?->id,
                'coupon_discount' => $summary->couponDiscount,
                'discount' => $summary->discount,
                'shipping_cost' => $shippingCost,
                'total_products_price' => $summary->itemsTotal,
                'tax' => 0,
                'total_price' => $summary->payable + $shippingCost,
                'src' => OrderSrcEnum::WEB,
                'shipping_method_id' => $method->id,
            ]);

            foreach ($lines as $line) {
                /** @var CartLineDTO $line */
                $variety = $varieties->get($line->varietyId);

                $order->orderVarieties()->create([
                    'product_id' => $variety?->product_id,
                    'variety_id' => $line->varietyId,
                    'quantity' => $line->count,
                    'price' => $line->originalPrice,
                    'discount' => $line->lineOriginalTotal() - $line->lineTotal(),
                    'coupon_discount' => 0,
                    'final_price' => $line->lineTotal(),
                ]);
            }

            return $order;
        });
    }
}
