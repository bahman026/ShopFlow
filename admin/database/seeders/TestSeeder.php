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
            ReceiptSeeder::class,
            TransactionSeeder::class,
            GatewaySeeder::class,
            UserConfigSeeder::class,
            AddressSeeder::class,
            // ShippingLineSeeder/ShippingMethodSeeder/ShippingCitySeeder are
            // deliberately NOT called here: each does `Model::all()->each->delete()`
            // then creates 20 random rows, which wipes out ShippingSeeder's real,
            // checkout-critical shipping methods (called by DatabaseSeeder) and
            // replaces them with random fake ones (no nationwide fallback), so
            // the storefront checkout can no longer find a shipping method for
            // most addresses. Re-run ShippingSeeder if real shipping data is lost.
        ]);
    }
}
