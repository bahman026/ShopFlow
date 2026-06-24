<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\OrderVarietyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int $order_id
 * @property positive-int|null $product_id
 * @property positive-int|null $variety_id
 * @property positive-int|null $sub_order_id
 * @property positive-int $quantity
 * @property positive-int $gather_quantity
 * @property positive-int $invoice_quantity
 * @property positive-int $price
 * @property positive-int $discount
 * @property positive-int $coupon_discount
 * @property positive-int $final_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Order $order
 * @property Product|null $product
 * @property Variety|null $variety
 */
class OrderVariety extends Model
{
    /** @use HasFactory<OrderVarietyFactory> */
    use HasFactory;

    protected $table = 'order_varieties';

    protected $fillable = [
        'order_id',
        'product_id',
        'variety_id',
        'sub_order_id',
        'quantity',
        'gather_quantity',
        'invoice_quantity',
        'price',
        'discount',
        'coupon_discount',
        'final_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'gather_quantity' => 'integer',
        'invoice_quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }
}
