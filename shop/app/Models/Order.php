<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property string $tracking_code
 * @property positive-int|null $user_id
 * @property positive-int|null $address_id
 * @property OrderStatusEnum $status
 * @property positive-int|null $coupon_id
 * @property int $coupon_discount
 * @property int $discount
 * @property int $shipping_cost
 * @property int $total_products_price
 * @property int $tax
 * @property int $total_price
 * @property OrderSrcEnum $src
 * @property positive-int|null $shipping_method_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Address|null $address
 * @property ShippingMethod|null $shippingMethod
 * @property Collection<int, OrderVariety> $orderVarieties
 * @property Collection<int, Transaction> $transactions
 */
class Order extends Model
{
    protected $fillable = [
        'user_id',
        'address_id',
        'status',
        'coupon_id',
        'coupon_discount',
        'discount',
        'shipping_cost',
        'total_products_price',
        'tax',
        'total_price',
        'src',
        'shipping_method_id',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'src' => OrderSrcEnum::class,
        'coupon_discount' => 'integer',
        'discount' => 'integer',
        'shipping_cost' => 'integer',
        'total_products_price' => 'integer',
        'tax' => 'integer',
        'total_price' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->tracking_code ??= self::generateTrackingCode();
        });
    }

    /**
     * A random 10-digit number, not the sequential `id`, so a customer's
     * tracking code never reveals order volume/growth over time.
     */
    private static function generateTrackingCode(): string
    {
        do {
            $code = (string) random_int(1_000_000_000, 9_999_999_999);
        } while (self::query()->where('tracking_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function orderVarieties(): HasMany
    {
        return $this->hasMany(OrderVariety::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Whether the customer can safely retry payment on this order. Only true
     * for a CANCELED order whose latest transaction never actually captured
     * money (customer canceled at the gateway, or verification failed) —
     * never true for the oversold case (CompleteCheckoutPayment::
     * failPaidButOversold), where Zarinpal already captured payment and a
     * retry would risk charging the customer twice before a manual refund.
     * Repeated retries reuse this same order (RetryOrderPayment), each
     * adding its own Transaction row, so an order can accumulate several
     * transactions sharing the same `created_at` second in quick succession
     * — `id` breaks the tie deterministically (see GetUserOrders for the
     * same class of bug).
     */
    public function isRetryable(): bool
    {
        if ($this->status !== OrderStatusEnum::CANCELED) {
            return false;
        }

        /** @var Transaction|null $transaction */
        $transaction = $this->transactions()->latest()->orderByDesc('id')->first();

        return $transaction === null || $transaction->paid_at === null;
    }
}
