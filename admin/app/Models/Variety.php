<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VarietyStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property Product $product
 * @property string $attribute_value
 * @property string|null $color
 * @property positive-int $price
 * @property positive-int|null $sale_price
 * @property positive-int $inventory
 * @property bool $has_stock
 * @property VarietyStatusEnum $status
 */
class Variety extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_value',
        'color',
        'price',
        'sale_price',
        'inventory',
        'has_stock',
        'status',
    ];

    protected $casts = [
        'has_stock' => 'boolean',
        'status' => VarietyStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::saved(fn (Variety $variety) => $variety->syncProductVarietyCount());
        static::deleted(fn (Variety $variety) => $variety->syncProductVarietyCount());
    }

    public function syncProductVarietyCount(): void
    {
        /** @var Product|null $product */
        $product = $this->product()->first();

        if ($product === null) {
            return;
        }

        $product->forceFill([
            'variety_counts' => $product->varieties()->count(),
        ])->saveQuietly();
    }

    // Relationships

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
