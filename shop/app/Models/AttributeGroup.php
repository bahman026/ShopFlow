<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property string $name
 * @property Collection<int, Attribute> $attributes
 */
class AttributeGroup extends Model
{
    protected $fillable = [
        'name',
    ];

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }
}
