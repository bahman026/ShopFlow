<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Attribute
 *
 * @property positive-int $id
 * @property positive-int $attribute_group_id
 * @property string $value
 * @property AttributeGroup $attributeGroup
 */
class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_group_id',
        'value',
    ];

    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class);
    }
}
