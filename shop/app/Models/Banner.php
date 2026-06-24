<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BannerStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property positive-int $id
 * @property string $position
 * @property string $heading
 * @property string|null $url
 * @property positive-int|null $sort
 * @property BannerStatusEnum $status
 * @property Image|null $featuredImage
 * @property Collection<int, Image> $images
 */
class Banner extends Model
{
    protected $fillable = [
        'position',
        'heading',
        'url',
        'sort',
        'status',
    ];

    protected $casts = [
        'status' => BannerStatusEnum::class,
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', BannerStatusEnum::PUBLISHED->value);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function featuredImage(): HasOne
    {
        return $this->hasOne(Image::class, 'imageable_id')
            ->where('imageable_type', self::class)
            ->where('is_featured', true);
    }
}
