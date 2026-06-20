<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property positive-int $id
 * @property positive-int $shipping_method_id
 * @property positive-int|null $province_id
 * @property positive-int|null $city_id
 * @property bool $pay_on_delivery
 * @property positive-int|null $amount
 * @property string|null $sending_days
 * @property Carbon|null $disable_from
 * @property Carbon|null $disable_to
 * @property positive-int|null $delay
 * @property string|null $description
 * @property bool $status
 * @property ShippingMethod $shippingMethod
 * @property Province|null $province
 * @property City|null $city
 */
class ShippingCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_method_id',
        'province_id',
        'city_id',
        'pay_on_delivery',
        'amount',
        'sending_days',
        'disable_from',
        'disable_to',
        'delay',
        'description',
        'status',
    ];

    protected $casts = [
        'pay_on_delivery' => 'boolean',
        'status' => 'boolean',
        'disable_from' => 'datetime',
        'disable_to' => 'datetime',
    ];

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
