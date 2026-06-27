<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property positive-int $shipping_line_id
 * @property string $name
 * @property string|null $type
 * @property positive-int|null $min_count
 * @property positive-int|null $min_amount
 * @property int $for
 * @property bool $status
 * @property ShippingLine $shippingLine
 * @property Collection<int, ShippingCity> $shippingCities
 */
class ShippingMethod extends Model
{
    protected $fillable = [
        'shipping_line_id',
        'name',
        'type',
        'min_count',
        'min_amount',
        'for',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(ShippingLine::class);
    }

    public function shippingCities(): HasMany
    {
        return $this->hasMany(ShippingCity::class);
    }
}
