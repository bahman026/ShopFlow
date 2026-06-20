<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Attribute
 *
 * @property positive-int $id
 * @property positive-int $attribute_group_id
 * @property string|null $color
 * @property string $value
 * @property AttributeGroup $attributeGroup
 * @property Collection<Variety> $varieties
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

    public function varieties(): BelongsToMany
    {
        return $this->belongsToMany(Variety::class)->withTimestamps();
    }
}
