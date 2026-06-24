<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use Carbon\Carbon;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int|null $user_id
 * @property positive-int|null $order_id
 * @property string|null $ref_id
 * @property positive-int $amount
 * @property positive-int|null $accounting_id
 * @property TransactionPortEnum|null $port
 * @property string|null $transaction_id
 * @property TransactionStatusEnum $status
 * @property string|null $ip
 * @property string|null $description
 * @property Carbon|null $paid_at
 * @property string|null $result_code
 * @property string|null $result_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Order|null $order
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'ref_id',
        'amount',
        'accounting_id',
        'port',
        'transaction_id',
        'status',
        'ip',
        'description',
        'paid_at',
        'result_code',
        'result_message',
    ];

    protected $casts = [
        'port' => TransactionPortEnum::class,
        'status' => TransactionStatusEnum::class,
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
