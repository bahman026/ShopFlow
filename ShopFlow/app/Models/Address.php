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
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
