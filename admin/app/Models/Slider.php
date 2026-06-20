<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SliderStatusEnum;
use Database\Factories\SliderFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property string $name
 * @property string $position
 * @property SliderStatusEnum $status
 * @property Collection<Slide> $slides
 */
class Slider extends Model
{
    /** @use HasFactory<SliderFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => SliderStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (Slider $slider): void {
            $slider->slides->each->delete();
        });
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }
}
