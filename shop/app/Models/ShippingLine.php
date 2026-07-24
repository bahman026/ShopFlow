<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property string $name
 * @property positive-int $cost
 * @property Collection<int, ShippingMethod> $shippingMethods
 */
class ShippingLine extends Model
{
    protected $fillable = [
        'name',
        'cost',
    ];

    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class);
    }
}
