<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property positive-int $id
 * @property string $name
 * @property string $code
 * @property positive-int $amount
 * @property positive-int|null $min_price
 * @property positive-int|null $max_discount
 * @property positive-int $total_used
 * @property positive-int|null $total_uses
 * @property positive-int|null $user_id
 * @property positive-int|null $user_creator_id
 * @property CouponStatusEnum $status
 * @property bool $is_percent
 * @property bool $shipping
 * @property CouponForEnum $is_for
 * @property Carbon|null $started_at
 * @property Carbon|null $expired_at
 * @property User|null $user
 * @property Collection<Product> $products
 * @property Collection<Variety> $varieties
 * @property Collection<Category> $categories
 */
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'amount',
        'min_price',
        'max_discount',
        'total_used',
        'total_uses',
        'user_id',
        'user_creator_id',
        'status',
        'is_percent',
        'shipping',
        'is_for',
        'started_at',
        'expired_at',
    ];

    protected $casts = [
        'is_percent' => 'boolean',
        'shipping' => 'boolean',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'status' => CouponStatusEnum::class,
        'is_for' => CouponForEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_creator_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product')
            ->withTimestamps();
    }

    public function varieties(): BelongsToMany
    {
        return $this->belongsToMany(Variety::class, 'coupon_variety')
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_coupon')
            ->withTimestamps();
    }
}
