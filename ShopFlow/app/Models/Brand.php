<?php

namespace App\Models;

use App\Enums\BrandStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * App\Models\Brand
 *
 * @property positive-int $id
 * @property string $heading
 * @property string $slug
 * @property string|null $content
 * @property string|null $title
 * @property string|null $description
 * @property bool $no_index
 * @property string|null $canonical
 * @property positive-int|null $image_id
 * @property BrandStatusEnum $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
// * @property Image|null $image
 */

class Brand extends Model
{

    protected $fillable = [
        'heading',
        'slug',
        'content',
        'title',
        'description',
        'no_index',
        'canonical',
        'image_id',
        'status',
    ];

    protected $casts = [
        'no_index' => 'boolean',
        'status' => BrandStatusEnum::class,
    ];
    /**
     * Get all of the Brand's image.
     */
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
