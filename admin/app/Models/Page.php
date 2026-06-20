<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PageStatusEnum;
use Carbon\Carbon;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property positive-int $id
 * @property string $heading
 * @property string $slug
 * @property string|null $content
 * @property string|null $title
 * @property string|null $description
 * @property bool $no_index
 * @property string|null $canonical
 * @property PageStatusEnum $status
 * @property Carbon|null $published_at
 * @property Image|null $image
 */
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected $fillable = [
        'heading',
        'slug',
        'content',
        'title',
        'description',
        'no_index',
        'canonical',
        'status',
        'published_at',
    ];

    protected $casts = [
        'no_index' => 'boolean',
        'status' => PageStatusEnum::class,
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Page $page): void {
            $page->image?->delete();
        });
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
