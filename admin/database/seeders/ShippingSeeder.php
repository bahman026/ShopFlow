<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Province;
use App\Models\ShippingCity;
use App\Models\ShippingLine;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    /**
     * Seed the three storefront shipping methods. Costs are placeholder demo
     * values; descriptions and delivery windows match the storefront design.
     */
    public function run(): void
    {
        ShippingCity::query()->delete();
        ShippingMethod::query()->delete();
        ShippingLine::query()->delete();

        $tehran = Province::query()->where('name', 'تهران')->value('id');

        // پیک ویژه تهران: same-day style courier, Tehran only.
        $courier = ShippingLine::query()->create([
            'name' => 'پیک ویژه تهران',
            'cost' => 50000,
        ]);
        $courierMethod = ShippingMethod::query()->create([
            'shipping_line_id' => $courier->id,
            'name' => 'پیک ویژه تهران',
            'type' => 'پیک',
            'status' => true,
        ]);
        ShippingCity::query()->create([
            'shipping_method_id' => $courierMethod->id,
            'province_id' => $tehran,
            'amount' => 50000,
            'sending_days' => '۲۴ ساعت کاری',
            'description' => 'تحویل ۲۴ ساعت کاری پس از ثبت سفارش (روزهای تعطیل جزو زمان آماده‌سازی و ارسال محاسبه نمی‌شوند). سفارش‌هایی که در روزهای تعطیل رسمی ثبت شوند، در اولین روز کاری بعد پردازش و روز کاری پس از آن ارسال خواهند شد.',
            'status' => true,
        ]);

        // پست پیشتاز: nationwide.
        $post = ShippingLine::query()->create([
            'name' => 'پست',
            'cost' => 45000,
        ]);
        $postMethod = ShippingMethod::query()->create([
            'shipping_line_id' => $post->id,
            'name' => 'پست پیشتاز',
            'type' => 'پست',
            'status' => true,
        ]);
        ShippingCity::query()->create([
            'shipping_method_id' => $postMethod->id,
            'amount' => 45000,
            'sending_days' => '۲ تا ۴ روز کاری',
            'description' => 'ارسال از طریق پست پیشتاز (۲ تا ۴ روز کاری).',
            'status' => true,
        ]);

        // تحویل حضوری از فروشگاه: nationwide, postpaid (pay on delivery).
        $pickup = ShippingLine::query()->create([
            'name' => 'تحویل حضوری از فروشگاه',
            'cost' => 0,
        ]);
        $pickupMethod = ShippingMethod::query()->create([
            'shipping_line_id' => $pickup->id,
            'name' => 'تحویل حضوری از فروشگاه',
            'type' => 'حضوری',
            'status' => true,
        ]);
        ShippingCity::query()->create([
            'shipping_method_id' => $pickupMethod->id,
            'pay_on_delivery' => true,
            'amount' => null,
            'description' => 'تحویل حضوری (شنبه تا چهارشنبه، به‌جز روزهای تعطیل) — بعد از آماده‌سازی، زمان تحویل با شما از طرف فروشگاه هماهنگ می‌شود. هزینه ارسال به صورت پس‌کرایه می‌باشد.',
            'status' => true,
        ]);
    }
}
