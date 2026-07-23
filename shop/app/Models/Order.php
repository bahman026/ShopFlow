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
 * @property positive-int|null $user_id
 * @property positive-int|null $address_id
 * @property OrderStatusEnum $status
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
}
