<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Ancestor
 *
 * @property positive-int $id
 * @property string $name
 * @property int|null $order
 * @property Collection<AttributeGroup> $attributeGroups
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Ancestor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
    ];

    public function attributeGroups(): HasMany
    {
        return $this->hasMany(AttributeGroup::class);
    }
}
