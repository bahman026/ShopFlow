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
        ]);
    }
}
