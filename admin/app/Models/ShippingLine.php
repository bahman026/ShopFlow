<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property positive-int $id
 * @property string $name
 * @property positive-int $cost
 */
class ShippingLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cost',
    ];
}
