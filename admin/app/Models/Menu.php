<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property positive-int $id
 * @property string $name
 * @property string $position
 * @property bool $status
 * @property Collection<MenuItem> $menuItems
 */
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Menu $menu): void {
            $menu->menuItems->each->delete();
        });
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
