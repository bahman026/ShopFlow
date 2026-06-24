<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property positive-int $id
 * @property string $key
 * @property string|null $label
 * @property string|null $content
 * @property bool $autoload
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'content',
        'autoload',
    ];

    protected $casts = [
        'autoload' => 'boolean',
    ];

    public function scopeAutoloaded(Builder $query): Builder
    {
        return $query->where('autoload', true);
    }
}
