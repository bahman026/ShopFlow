<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, City> $cities
 */
class Province extends Model
{
    protected $fillable = [
        'name',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
