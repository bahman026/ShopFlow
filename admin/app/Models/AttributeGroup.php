<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\AttributeGroup
 *
 * @property positive-int $id
 * @property positive-int $ancestor_id
 * @property string $name
 * @property string|null $label
 * @property int|null $order
 * @property Ancestor $ancestor
 * @property Collection<Attribute> $attributes
 * @property Collection<Category> $categories
 * @property Collection<Product> $products
 */
class AttributeGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'ancestor_id',
        'name',
        'label',
        'order',
    ];

    public function ancestor(): BelongsTo
    {
        return $this->belongsTo(Ancestor::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'attribute_group_category')
            ->withPivot(['as_filter', 'required', 'order'])
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
