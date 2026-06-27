<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One cart line: a single variety with a quantity, owned by a logged-in user
 * or a guest session. A cart never touches inventory (see docs/ORDER.md).
 *
 * @property positive-int $id
 * @property positive-int|null $user_id
 * @property string|null $session_id
 * @property positive-int $variety_id
 * @property positive-int $count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Variety $variety
 * @property User|null $user
 */
class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'variety_id',
        'count',
    ];

    protected $casts = [
        'count' => 'integer',
    ];

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
