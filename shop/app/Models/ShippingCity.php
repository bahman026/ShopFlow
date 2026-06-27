<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int $shipping_method_id
 * @property positive-int|null $province_id
 * @property positive-int|null $city_id
 * @property bool $pay_on_delivery
 * @property positive-int|null $amount
 * @property string|null $sending_days
 * @property string|null $description
 * @property bool $status
 * @property ShippingMethod $shippingMethod
 */
class ShippingCity extends Model
{
    protected $fillable = [
        'shipping_method_id',
        'province_id',
        'city_id',
        'pay_on_delivery',
        'amount',
        'sending_days',
        'description',
        'status',
    ];

    protected $casts = [
        'pay_on_delivery' => 'boolean',
        'status' => 'boolean',
    ];

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}
