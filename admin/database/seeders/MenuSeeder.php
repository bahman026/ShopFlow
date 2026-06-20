<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        MenuItem::all()->each->delete();
        Menu::query()->truncate();

        Menu::factory()->count(5)->create()->each(function (Menu $menu): void {
            $parents = MenuItem::factory()->count(6)->create(['menu_id' => $menu->id]);

            $parents->each(function (MenuItem $parent): void {
                MenuItem::factory()->count(3)->withParent($parent)->create();
            });
        });
    }
}
