<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\AttributeGroup
 *
 * @property positive-int $id
 * @property positive-int $ancestor_id
 * @property string $name
 * @property Ancestor $ancestor
 * @property string|null $label
 * @property int|null $order
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

    protected $casts = [
        'is_selective' => 'boolean',
    ];

    public function ancestor(): BelongsTo
    {
        return $this->belongsTo(Ancestor::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }
}
