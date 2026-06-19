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
 * @property string|null $alt_text
 * @property bool $is_featured
 * @property positive-int|null $order
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
        'alt_text',
        'is_featured',
        'order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (Image $image): void {
            if (! $image->is_featured) {
                return;
            }

            static::query()
                ->where('imageable_type', $image->imageable_type)
                ->where('imageable_id', $image->imageable_id)
                ->whereKeyNot($image->getKey())
                ->update(['is_featured' => false]);
        });
    }

    /**
     * Get the parent imageable model (post or video).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
