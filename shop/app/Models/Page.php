<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PageStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Image|null $image
 */
class Page extends Model
{
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PageStatusEnum::PUBLISHED->value);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
