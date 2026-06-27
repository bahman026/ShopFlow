<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property string $name
 * @property positive-int $province_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Province $province
 */
class City extends Model
{
    protected $fillable = [
        'name',
        'province_id',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
