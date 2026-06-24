<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property positive-int $id
 * @property string $path
 * @property positive-int $imageable_id
 * @property string $imageable_type
 * @property string|null $alt_text
 * @property bool $is_featured
 * @property positive-int|null $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Image extends Model
{
    protected $fillable = [
        'path',
        'imageable_id',
        'imageable_type',
        'alt_text',
        'is_featured',
        'order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
