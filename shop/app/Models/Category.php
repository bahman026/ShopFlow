<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property positive-int|null $parent_id
 * @property CategoryStatusEnum $status
 * @property Image|null $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, Product> $products
 * @property Category|null $parent
 * @property Collection<int, Category> $children
 */
class Category extends Model
{
    protected $fillable = [
        'heading',
        'slug',
        'content',
        'title',
        'description',
        'no_index',
        'canonical',
        'parent_id',
        'status',
    ];

    protected $casts = [
        'no_index' => 'boolean',
        'status' => CategoryStatusEnum::class,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CategoryStatusEnum::ACTIVE->value);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
