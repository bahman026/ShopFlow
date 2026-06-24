<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\OrderNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int $order_id
 * @property positive-int|null $user_id
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Order $order
 * @property User|null $user
 */
class OrderNote extends Model
{
    /** @use HasFactory<OrderNoteFactory> */
    use HasFactory;

    protected $table = 'order_notes';

    protected $fillable = [
        'order_id',
        'user_id',
        'content',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
