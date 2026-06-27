<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property positive-int $id
 * @property string $heading
 * @property string $slug
 * @property positive-int $price
 * @property string|null $content
 * @property string|null $title
 * @property string|null $description
 * @property bool $no_index
 * @property string|null $canonical
 * @property positive-int|null $image_id
 * @property positive-int|null $attribute_group_id
 * @property positive-int|null $category_id
 * @property positive-int|null $brand_id
 * @property positive-int|null $minimum
 * @property positive-int|null $maximum
 * @property positive-int|null $step
 * @property positive-int|null $profit_percent
 * @property bool $has_stock
 * @property positive-int|null $variety_counts
 * @property positive-int|null $weight
 * @property positive-int|null $length
 * @property positive-int|null $width
 * @property positive-int|null $height
 * @property ProductStatusEnum $status
 * @property positive-int $seen
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Image|null $featuredImage
 * @property Collection<int, Image> $images
 * @property Collection<int, Variety> $varieties
 * @property Collection<int, Review> $reviews
 * @property Collection<int, Attribute> $attributes
 * @property Category|null $category
 * @property Brand|null $brand
 */
class Product extends Model
{
    protected $fillable = [
        'heading',
        'slug',
        'price',
        'content',
        'title',
        'description',
        'no_index',
        'canonical',
        'image_id',
        'attribute_group_id',
        'category_id',
        'brand_id',
        'minimum',
        'maximum',
        'step',
        'profit_percent',
        'has_stock',
        'variety_counts',
        'weight',
        'length',
        'width',
        'height',
        'status',
        'seen',
    ];

    protected $casts = [
        'no_index' => 'boolean',
        'has_stock' => 'boolean',
        'status' => ProductStatusEnum::class,
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatusEnum::PUBLISHED->value);
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

    public function varieties(): HasMany
    {
        return $this->hasMany(Variety::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute')
            ->withPivot('is_highlight')
            ->withTimestamps();
    }

    public function highlights(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute')
            ->wherePivot('is_highlight', true)
            ->withPivot('is_highlight')
            ->withTimestamps();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
