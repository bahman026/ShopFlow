<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VarietyStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property positive-int $id
 * @property positive-int $product_id
 * @property positive-int|null $attribute_id
 * @property string|null $attribute_value
 * @property string|null $color
 * @property positive-int $price
 * @property positive-int|null $sale_price
 * @property int<0, max> $inventory
 * @property bool $has_stock
 * @property VarietyStatusEnum $status
 * @property Product $product
 * @property Attribute|null $attribute
 * @property Collection<int, Attribute> $attributes
 * @property Collection<int, Review> $reviews
 * @property Image|null $image
 */
class Variety extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value',
        'color',
        'price',
        'sale_price',
        'inventory',
        'has_stock',
        'status',
    ];

    protected $casts = [
        'has_stock' => 'boolean',
        'status' => VarietyStatusEnum::class,
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', VarietyStatusEnum::PUBLISHED->value);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class)->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
