<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            VarietySeeder::class,
            DiscountSeeder::class,
            CouponSeeder::class,
            BannerSeeder::class,
            SliderSeeder::class,
            MenuSeeder::class,
            PageSeeder::class,
            FaqSeeder::class,
            ReviewSeeder::class,
            WishlistSeeder::class,
            CartSeeder::class,
            OrderSeeder::class,
            OrderVarietySeeder::class,
            OrderShippingSeeder::class,
            OrderNoteSeeder::class,
            ShippingLineSeeder::class,
            ShippingMethodSeeder::class,
            ShippingCitySeeder::class,
        ]);
    }
}
