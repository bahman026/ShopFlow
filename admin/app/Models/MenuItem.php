<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property positive-int $id
 * @property positive-int $menu_id
 * @property positive-int|null $parent_id
 * @property string $name
 * @property string|null $url
 * @property string|null $label
 * @property positive-int $order
 * @property Menu $menu
 * @property MenuItem|null $parent
 * @property Collection<MenuItem> $children
 * @property Image|null $image
 */
class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'name',
        'url',
        'label',
        'order',
    ];

    protected static function booted(): void
    {
        static::deleting(function (MenuItem $item): void {
            $item->children->each->delete();
            $item->image?->delete();
        });
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
