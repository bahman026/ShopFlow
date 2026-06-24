<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderShippingPaymentTypeEnum;
use Carbon\Carbon;
use Database\Factories\OrderShippingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int $order_id
 * @property positive-int|null $checker_id
 * @property positive-int|null $pack_count
 * @property positive-int|null $pack_user
 * @property Carbon|null $checked_at
 * @property Carbon|null $shipped_at
 * @property positive-int|null $sender_id
 * @property positive-int|null $courier_id
 * @property Carbon|null $courier_received_at
 * @property Carbon|null $courier_delivered_at
 * @property string|null $confirm_code
 * @property bool $cheque_is_require
 * @property positive-int|null $courier2_id
 * @property string|null $description
 * @property OrderShippingPaymentTypeEnum|null $payment_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Order $order
 * @property User|null $checker
 * @property User|null $packer
 * @property User|null $sender
 * @property User|null $courier
 * @property User|null $courier2
 */
class OrderShipping extends Model
{
    /** @use HasFactory<OrderShippingFactory> */
    use HasFactory;

    protected $table = 'order_shippings';

    protected $fillable = [
        'order_id',
        'checker_id',
        'pack_count',
        'pack_user',
        'checked_at',
        'shipped_at',
        'sender_id',
        'courier_id',
        'courier_received_at',
        'courier_delivered_at',
        'confirm_code',
        'cheque_is_require',
        'courier2_id',
        'description',
        'payment_type',
    ];

    protected $casts = [
        'pack_count' => 'integer',
        'checked_at' => 'datetime',
        'shipped_at' => 'datetime',
        'courier_received_at' => 'datetime',
        'courier_delivered_at' => 'datetime',
        'cheque_is_require' => 'boolean',
        'payment_type' => OrderShippingPaymentTypeEnum::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pack_user');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function courier2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier2_id');
    }
}
