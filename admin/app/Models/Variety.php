<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VarietyStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int $product_id
 * @property positive-int|null $attribute_id
 * @property string|null $attribute_value
 * @property string|null $color
 * @property positive-int $price
 * @property positive-int|null $sale_price
 * @property positive-int $inventory
 * @property bool $has_stock
 * @property VarietyStatusEnum $status
 * @property Product $product
 * @property Attribute|null $attribute
 */
class Variety extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
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
        static::saving(function (Variety $variety): void {
            if ($variety->attribute_id === null) {
                return;
            }
            $attribute = Attribute::find($variety->attribute_id);
            if ($attribute === null) {
                return;
            }
            $variety->attribute_value = $attribute->value;
            $variety->color = $attribute->color;
        });

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
