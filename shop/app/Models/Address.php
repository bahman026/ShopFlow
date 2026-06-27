<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property positive-int $id
 * @property string $name
 * @property string $phone
 * @property string|null $postal_code
 * @property string $address
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $description
 * @property positive-int $city_id
 * @property positive-int $user_id
 * @property bool $prime
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property City $city
 * @property User $user
 */
class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'postal_code',
        'address',
        'latitude',
        'longitude',
        'description',
        'city_id',
        'user_id',
        'prime',
    ];

    protected $casts = [
        'prime' => 'boolean',
    ];

    protected static function booted(): void
    {
        // One primary address per user: when an address is saved as primary,
        // demote the user's other addresses (mirrors the admin model).
        static::saved(function (Address $address): void {
            if ($address->prime) {
                static::query()
                    ->where('user_id', $address->user_id)
                    ->whereKeyNot($address->getKey())
                    ->where('prime', true)
                    ->update(['prime' => false]);
            }
        });
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
