<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SliderStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property string $name
 * @property string $position
 * @property SliderStatusEnum $status
 * @property Collection<int, Slide> $slides
 */
class Slider extends Model
{
    protected $fillable = [
        'name',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => SliderStatusEnum::class,
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', SliderStatusEnum::PUBLISHED->value);
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }
}
