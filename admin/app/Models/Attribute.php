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
 * App\Models\Attribute
 *
 * @property positive-int $id
 * @property positive-int $attribute_group_id
 * @property string|null $color
 * @property string $value
 * @property AttributeGroup $attributeGroup
 * @property Collection<Variety> $varieties
 * @property Collection<Variety> $directVarieties
 * @property Collection<Product> $products
 */
class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_group_id',
        'value',
        'color',
    ];

    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    /** Varieties linked via the attribute_variety pivot (additional attributes). */
    public function varieties(): BelongsToMany
    {
        return $this->belongsToMany(Variety::class)->withTimestamps();
    }

    /** Varieties where this attribute is the primary one (attribute_id FK). */
    public function directVarieties(): HasMany
    {
        return $this->hasMany(Variety::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute')
            ->withPivot('is_highlight')
            ->withTimestamps();
    }
}
