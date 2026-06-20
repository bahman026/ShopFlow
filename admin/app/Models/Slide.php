<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SlideFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property positive-int $id
 * @property positive-int $slider_id
 * @property string|null $heading
 * @property string|null $label
 * @property string|null $url
 * @property positive-int $order
 * @property Slider $slider
 * @property Image|null $image
 */
class Slide extends Model
{
    /** @use HasFactory<SlideFactory> */
    use HasFactory;

    protected $fillable = [
        'slider_id',
        'heading',
        'label',
        'url',
        'order',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Slide $slide): void {
            $slide->image?->delete();
        });
    }

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
