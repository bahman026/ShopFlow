<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->settings() as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'label' => $setting['label'],
                    'content' => $setting['content'],
                    'autoload' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array{key: string, label: string, content: string}>
     */
    private function settings(): array
    {
        return [
            [
                'key' => 'site_name',
                'label' => 'نام فروشگاه',
                'content' => 'فروشگاه اینترنتی شاپ‌فلو',
            ],
            [
                'key' => 'site_phone',
                'label' => 'شماره تماس',
                'content' => '021-91000000',
            ],
            [
                'key' => 'site_email',
                'label' => 'ایمیل',
                'content' => 'support@shopflow.ir',
            ],
            [
                'key' => 'site_address',
                'label' => 'آدرس',
                'content' => 'تهران، خیابان ولیعصر، بالاتر از میدان ونک، کوچه نگار، پلاک ۲۴، طبقه سوم، کد پستی: 1990783412',
            ],
            [
                'key' => 'site_working_hours',
                'label' => 'ساعات کاری',
                'content' => $this->json([
                    'شنبه تا چهارشنبه از ساعت ۰۹:۰۰ الی ۱۸',
                    'پنجشنبه و ایام تعطیل رسمی به جز جمعه‌ها از ساعت ۰۹:۰۰ الی ۱۴',
                ]),
            ],
            [
                'key' => 'footer_about',
                'label' => 'درباره فروشگاه (فوتر)',
                'content' => 'فروشگاه اینترنتی شاپ‌فلو که فعالیت خود را از سال ۱۳۹۸ آغاز کرده است، امروز به یک فروشگاه چندمنظوره و جامع تبدیل شده است. ما در شاپ‌فلو طیف گسترده‌ای از محصولات شامل پوشاک و لوازم متنوع برای مردان، زنان و کودکان، عطر و لوازم آرایشی و بهداشتی، کالای خواب و لوازم خانه و آشپزخانه، لوازم الکترونیک، تجهیزات ورزشی و ملزومات سفر را با بهترین قیمت در اختیار شما قرار می‌دهیم. فلسفه ما ایجاد یک تجربه خرید آنلاین سریع، آسان و مطمئن است که بر سه اصل استوار است: روش‌های پرداخت متنوع، هفت روز ضمانت بازگشت کالا و تضمین اصالت محصولات. هدف ما از روز نخست چیزی فراتر از فروش بوده است؛ ما به دنبال جلب رضایت مشتریان و بهبود مداوم کیفیت خدمات هستیم.',
            ],
            [
                'key' => 'footer_copyright',
                'label' => 'متن کپی‌رایت',
                'content' => 'تمامی حقوق این وب‌سایت متعلق به فروشگاه اینترنتی شاپ‌فلو است.',
            ],
            [
                'key' => 'footer_links_shop',
                'label' => 'لینک‌های فروشگاه (فوتر)',
                'content' => $this->json([
                    ['label' => 'وبلاگ', 'url' => '/blog'],
                    ['label' => 'مردانه', 'url' => '/men'],
                    ['label' => 'زنانه', 'url' => '/women'],
                    ['label' => 'بچگانه', 'url' => '/kids'],
                    ['label' => 'آرایشی و بهداشتی', 'url' => '/beauty'],
                    ['label' => 'خرید هدیه', 'url' => '/gifts'],
                ]),
            ],
            [
                'key' => 'footer_links_support',
                'label' => 'لینک‌های پشتیبانی (فوتر)',
                'content' => $this->json([
                    ['label' => 'درباره ما', 'url' => '/about-us'],
                    ['label' => 'پرسش‌های متداول', 'url' => '/faq'],
                    ['label' => 'شرایط بازگشت کالا', 'url' => '/return-conditions'],
                    ['label' => 'حریم خصوصی', 'url' => '/privacy'],
                    ['label' => 'همکاری با ما', 'url' => '/cooperation'],
                ]),
            ],
            [
                'key' => 'footer_socials',
                'label' => 'شبکه‌های اجتماعی (فوتر)',
                'content' => $this->json([
                    ['name' => 'اینستاگرام', 'url' => 'https://instagram.com/shopflow'],
                    ['name' => 'تلگرام', 'url' => 'https://t.me/shopflow'],
                    ['name' => 'لینکدین', 'url' => 'https://linkedin.com/company/shopflow'],
                ]),
            ],
        ];
    }

    /**
     * @param  array<int|string, mixed>  $value
     */
    private function json(array $value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
