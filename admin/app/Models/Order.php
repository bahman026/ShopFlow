<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property positive-int|null $user_id
 * @property positive-int|null $coupon_id
 * @property OrderStatusEnum $status
 * @property positive-int $coupon_discount
 * @property positive-int $discount
 * @property positive-int $shipping_cost
 * @property positive-int $total_products_price
 * @property positive-int $tax
 * @property positive-int $total_price
 * @property bool $for_partner
 * @property string|null $content
 * @property OrderSrcEnum $src
 * @property string|null $version
 * @property positive-int|null $bijack_image_id
 * @property string|null $bijack_description
 * @property positive-int|null $accounting_id
 * @property positive-int|null $confirmed_id
 * @property Carbon|null $confirmed_at
 * @property string|null $confirm_description
 * @property positive-int|null $collector_id
 * @property Carbon|null $collected_at
 * @property Carbon|null $collector_reminded_at
 * @property string|null $collector_description
 * @property positive-int|null $notifier_id
 * @property Carbon|null $notified_at
 * @property positive-int|null $shipping_line_id
 * @property positive-int|null $shipping_method_id
 * @property string|null $send_description
 * @property string|null $receive_from
 * @property string|null $receive_to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Coupon|null $coupon
 * @property User|null $confirmer
 * @property User|null $collector
 * @property User|null $notifier
 * @property ShippingLine|null $shippingLine
 * @property ShippingMethod|null $shippingMethod
 * @property Collection<OrderVariety> $orderVarieties
 * @property Collection<OrderNote> $orderNotes
 * @property Collection<OrderShipping> $orderShippings
 * @property Collection<Receipt> $receipts
 * @property Collection<Transaction> $transactions
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'status',
        'coupon_discount',
        'discount',
        'shipping_cost',
        'total_products_price',
        'tax',
        'total_price',
        'for_partner',
        'content',
        'src',
        'version',
        'bijack_image_id',
        'bijack_description',
        'accounting_id',
        'confirmed_id',
        'confirmed_at',
        'confirm_description',
        'collector_id',
        'collected_at',
        'collector_reminded_at',
        'collector_description',
        'notifier_id',
        'notified_at',
        'shipping_line_id',
        'shipping_method_id',
        'send_description',
        'receive_from',
        'receive_to',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'src' => OrderSrcEnum::class,
        'for_partner' => 'boolean',
        'confirmed_at' => 'datetime',
        'collected_at' => 'datetime',
        'collector_reminded_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function notifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifier_id');
    }

    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(ShippingLine::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function orderVarieties(): HasMany
    {
        return $this->hasMany(OrderVariety::class);
    }

    public function orderNotes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }

    public function orderShippings(): HasMany
    {
        return $this->hasMany(OrderShipping::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
