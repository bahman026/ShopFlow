<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int $order_id
 * @property positive-int|null $product_id
 * @property positive-int|null $variety_id
 * @property int $quantity
 * @property int $price
 * @property int $discount
 * @property int $coupon_discount
 * @property int $final_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Order $order
 * @property Product|null $product
 * @property Variety|null $variety
 */
class OrderVariety extends Model
{
    protected $table = 'order_varieties';

    protected $fillable = [
        'order_id',
        'product_id',
        'variety_id',
        'quantity',
        'price',
        'discount',
        'coupon_discount',
        'final_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
        'discount' => 'integer',
        'coupon_discount' => 'integer',
        'final_price' => 'integer',
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
