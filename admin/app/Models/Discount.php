<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DiscountForEnum;
use Database\Factories\DiscountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property positive-int $id
 * @property positive-int $variety_id
 * @property positive-int $quantity
 * @property positive-int $priority
 * @property bool $is_percent
 * @property positive-int $amount
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property positive-int $sold
 * @property positive-int|null $max_sell
 * @property positive-int|null $max_sell_by_user
 * @property DiscountForEnum $is_for
 * @property Variety $variety
 */
class Discount extends Model
{
    /** @use HasFactory<DiscountFactory> */
    use HasFactory;

    protected $fillable = [
        'variety_id',
        'quantity',
        'priority',
        'is_percent',
        'amount',
        'started_at',
        'ended_at',
        'sold',
        'max_sell',
        'max_sell_by_user',
        'is_for',
    ];

    protected $casts = [
        'is_percent' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_for' => DiscountForEnum::class,
    ];

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }
}
