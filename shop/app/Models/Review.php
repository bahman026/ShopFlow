<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property string $heading
 * @property string $content
 * @property positive-int|null $user_id
 * @property positive-int $product_id
 * @property positive-int|null $variety_id
 * @property positive-int|null $parent_id
 * @property ReviewStatusEnum $status
 * @property User|null $user
 * @property Product $product
 * @property Variety|null $variety
 * @property Review|null $parent
 * @property Collection<int, Review> $replies
 */
class Review extends Model
{
    protected $fillable = [
        'heading',
        'content',
        'user_id',
        'product_id',
        'variety_id',
        'parent_id',
        'status',
    ];

    protected $casts = [
        'status' => ReviewStatusEnum::class,
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ReviewStatusEnum::APPROVED->value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Review::class, 'parent_id');
    }
}
