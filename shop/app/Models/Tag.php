<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * An SEO landing page for a category and/or attribute filter (docs/TAGS.md).
 * Resolves to products in its category (and descendants) carrying its
 * attribute(s) — there is no direct product↔tag link. Category and
 * attributes are each optional (at least one is set).
 *
 * @property positive-int $id
 * @property string $name
 * @property string $slug
 * @property positive-int|null $category_id
 * @property string|null $content
 * @property string|null $title
 * @property string|null $description
 * @property bool $no_index
 * @property string|null $canonical
 * @property bool $show_on_home
 * @property int $home_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Category|null $category
 * @property Collection<int, Attribute> $attributes
 * @property Image|null $image
 */
class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'content',
        'title',
        'description',
        'no_index',
        'canonical',
        'show_on_home',
        'home_order',
    ];

    protected $casts = [
        'no_index' => 'boolean',
        'show_on_home' => 'boolean',
        'home_order' => 'integer',
    ];

    public function scopeOnHome(Builder $query): Builder
    {
        return $query->where('show_on_home', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class)->withTimestamps();
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
