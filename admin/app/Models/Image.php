<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\Image
 *
 * @property positive-int $id
 * @property string $path
 * @property positive-int $imageable_id
 * @property string $imageable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'imageable_id',
        'imageable_type',
    ];

    /**
     * Get the parent imageable model (post or video).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
