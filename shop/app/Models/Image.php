<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

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
 * @property-read string $url
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

    /**
     * Resolve the stored relative `path` into a full URL.
     *
     * `images.path` is relative to the image host (see the db doc); we prefix
     * it with `app.image_url` (the admin app's public storage) when set,
     * leaving already-absolute URLs untouched.
     */
    protected function url(): Attribute
    {
        return Attribute::get(function (): string {
            $path = (string) $this->path;

            if ($path === '' || Str::startsWith($path, ['http://', 'https://', '//'])) {
                return $path;
            }

            $base = rtrim((string) config('app.image_url'), '/');
            $path = ltrim($path, '/');

            return $base === '' ? '/'.$path : $base.'/'.$path;
        });
    }
}
