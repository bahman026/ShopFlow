<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Province
 *
 * @property positive-int $id
 * @property string $name
 * @property Collection<City> $cities
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
