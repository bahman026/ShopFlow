<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ShippingMethodForEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property positive-int $id
 * @property positive-int $shipping_line_id
 * @property string $name
 * @property string|null $type
 * @property positive-int|null $min_count
 * @property positive-int|null $min_amount
 * @property ShippingMethodForEnum $for
 * @property Carbon|null $disable_from
 * @property Carbon|null $disable_to
 * @property bool $status
 * @property ShippingLine $shippingLine
 * @property Collection<ShippingCity> $shippingCities
 */
class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_line_id',
        'name',
        'type',
        'min_count',
        'min_amount',
        'for',
        'disable_from',
        'disable_to',
        'status',
    ];

    protected $casts = [
        'for' => ShippingMethodForEnum::class,
        'status' => 'boolean',
        'disable_from' => 'datetime',
        'disable_to' => 'datetime',
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
