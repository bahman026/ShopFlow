<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AttributeGroupCategory
 *
 * @property int $id
 * @property int $attribute_group_id
 * @property int $category_id
 * @property bool $as_filter
 * @property bool $required
 * @property int|null $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AttributeGroupCategory extends Model
{
    use HasFactory;

    protected $table = 'attribute_group_category';

    protected $fillable = [
        'attribute_group_id',
        'category_id',
        'as_filter',
        'required',
        'order',
    ];

    /**
     * Define the relationship with the AttributeGroup model.
     */
    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    /**
     * Define the relationship with the Category model.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
