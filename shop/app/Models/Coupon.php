<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Read model for the admin-owned `coupons` table. A coupon is applied manually
 * by entering its code; the scope pivots (products / varieties / categories)
 * narrow which cart lines it may discount — no pivot rows at all means the
 * whole cart is eligible.
 *
 * Money columns are `decimal(12,2)` in the schema but Toman integers
 * everywhere in the app, so they are cast to int.
 *
 * @property positive-int $id
 * @property string $name
 * @property string $code
 * @property int $amount
 * @property int|null $min_price
 * @property int|null $max_discount
 * @property int $total_used
 * @property int|null $total_uses
 * @property positive-int|null $user_id
 * @property CouponStatusEnum $status
 * @property bool $is_percent
 * @property bool $shipping
 * @property CouponForEnum $is_for
 * @property Carbon|null $started_at
 * @property Carbon|null $expired_at
 * @property Collection<int, Product> $products
 * @property Collection<int, Variety> $varieties
 * @property Collection<int, Category> $categories
 */
class Coupon extends Model
{
    protected $casts = [
        'amount' => 'integer',
        'min_price' => 'integer',
        'max_discount' => 'integer',
        'total_used' => 'integer',
        'total_uses' => 'integer',
        'is_percent' => 'boolean',
        'shipping' => 'boolean',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'status' => CouponStatusEnum::class,
        'is_for' => CouponForEnum::class,
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function varieties(): BelongsToMany
    {
        return $this->belongsToMany(Variety::class, 'coupon_variety');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_coupon');
    }

    /**
     * Whether the coupon is limited to certain products, varieties or
     * categories rather than the whole cart.
     */
    public function isScoped(): bool
    {
        return $this->products->isNotEmpty()
            || $this->varieties->isNotEmpty()
            || $this->categories->isNotEmpty();
    }
}
