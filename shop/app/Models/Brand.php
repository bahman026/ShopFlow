<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BrandStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property positive-int $id
 * @property string $heading
 * @property string $slug
 * @property string|null $content
 * @property string|null $title
 * @property string|null $description
 * @property bool $no_index
 * @property string|null $canonical
 * @property BrandStatusEnum $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, Product> $products
 * @property Image|null $image
 */
class Brand extends Model
{
    protected $fillable = [
        'heading',
        'slug',
        'content',
        'title',
        'description',
        'no_index',
        'canonical',
        'status',
    ];

    protected $casts = [
        'no_index' => 'boolean',
        'status' => BrandStatusEnum::class,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', BrandStatusEnum::ACTIVE->value);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
