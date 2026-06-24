<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int|null $user_id
 * @property string|null $session_id
 * @property positive-int $variety_id
 * @property positive-int $count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Variety $variety
 */
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'variety_id',
        'count',
    ];

    protected $casts = [
        'count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }
}
