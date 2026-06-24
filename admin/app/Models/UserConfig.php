<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserConfigFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int $user_id
 * @property string $key
 * @property string|null $label
 * @property string|null $content
 * @property bool $autoload
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class UserConfig extends Model
{
    /** @use HasFactory<UserConfigFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'label',
        'content',
        'autoload',
    ];

    protected $casts = [
        'autoload' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
