<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Address
 *
 * @property positive-int $id
 * @property string $name
 * @property string $phone
 * @property string $postal_code
 * @property string $address
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
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'postal_code',
        'address',
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
        // Addresses are immutable history (orders reference them), so we never
        // mutate or delete them. We only keep a single primary per user: when
        // an address is saved as primary, demote the user's other addresses.
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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
