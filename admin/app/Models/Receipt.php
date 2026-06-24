<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReceiptTypeEnum;
use Carbon\Carbon;
use Database\Factories\ReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property positive-int $id
 * @property positive-int|null $user_id
 * @property positive-int|null $card_id
 * @property positive-int $amount
 * @property positive-int|null $order_id
 * @property string|null $tracking_code
 * @property Carbon|null $paid_at
 * @property string|null $destination_name
 * @property string|null $destination_bank
 * @property string|null $end_of_card_number
 * @property string|null $description
 * @property bool|null $is_paya
 * @property ReceiptTypeEnum $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Order|null $order
 * @property Image|null $image
 */
class Receipt extends Model
{
    /** @use HasFactory<ReceiptFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_id',
        'amount',
        'order_id',
        'tracking_code',
        'paid_at',
        'destination_name',
        'destination_bank',
        'end_of_card_number',
        'description',
        'is_paya',
        'type',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'is_paya' => 'boolean',
        'type' => ReceiptTypeEnum::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (Receipt $receipt): void {
            $receipt->image?->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
