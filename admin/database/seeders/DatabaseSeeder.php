<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminSeeder::class,
            CitySeeder::class,
            //            CategorySeeder::class,
            //            AncestorSeeder::class,
            //            AttributeSeeder::class,
            //            AttributeGroupCategorySeeder::class,
            ShippingSeeder::class,
            SettingSeeder::class,
        ]);

        // Realistic-looking demo catalog (categories, ~50 products, banners,
        // reviews, …) for local dev and staging demos — never production.
        if (app()->environment(['local', 'staging'])) {
            $this->call(DemoSeeder::class);
        }
    }
}
