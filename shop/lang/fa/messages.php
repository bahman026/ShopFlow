<?php

declare(strict_types=1);

return [
    // Generic flash/status messages shown after an action.
    'profile_saved' => 'اطلاعات حساب با موفقیت ذخیره شد.',

    'cart' => [
        'empty' => 'سبد خرید شما خالی است.',
        'added' => 'کالا به سبد خرید اضافه شد.',
        'removed' => 'کالا از سبد خرید حذف شد.',
        'unavailable' => 'این کالا موجود نیست.',
    ],

    'checkout' => [
        'stock_changed' => 'موجودی برخی از کالاهای سبد خرید شما تغییر کرده است. لطفاً سبد خرید را بررسی کنید.',
        'choose_address' => 'لطفاً نشانی ارسال را انتخاب کنید.',
        'choose_method' => 'لطفاً روش ارسال را انتخاب کنید.',
        'invalid_method' => 'روش ارسال انتخاب‌شده معتبر نیست.',
    ],

    'payment' => [
        'gateway_error' => 'در اتصال به درگاه پرداخت خطایی رخ داد. لطفاً دوباره تلاش کنید.',
        'failed' => 'پرداخت ناموفق بود.',
        'canceled_by_user' => 'پرداخت توسط کاربر لغو شد.',
        'verify_failed' => 'تأیید پرداخت ناموفق بود.',
        'paid_but_oversold' => 'پرداخت با موفقیت انجام شد ولی موجودی کالا کافی نبود — نیاز به بازگشت وجه به مشتری.',
        'order_number' => 'سفارش شماره :id',
    ],

    'address' => [
        'created' => 'نشانی با موفقیت ثبت شد.',
        'updated' => 'نشانی با موفقیت ویرایش شد.',
        'primary_changed' => 'نشانی پیش‌فرض تغییر کرد.',
        'deleted' => 'نشانی حذف شد.',
        'invalid_phone' => 'شماره موبایل معتبر نیست.',
        'invalid_postal_code' => 'کد پستی باید ۱۰ رقم باشد.',
    ],

    'auth' => [
        'code_still_valid' => 'کد قبلی هنوز معتبر است؛ لطفاً :seconds ثانیه دیگر برای دریافت کد جدید صبر کنید.',
        'code_invalid' => 'کد وارد شده نادرست یا منقضی شده است.',
        'password_invalid' => 'رمز عبور نادرست است.',
        'mobile_invalid' => 'شماره موبایل معتبر نیست.',
        'blocked' => 'حساب کاربری شما مسدود شده است.',
    ],

    'wishlist' => [
        'added' => 'به علاقه‌مندی‌ها اضافه شد.',
        'removed' => 'از علاقه‌مندی‌ها حذف شد.',
    ],

    'review' => [
        'submitted' => 'دیدگاه شما ثبت شد و پس از تأیید نمایش داده می‌شود.',
    ],

    'orders' => [
        'retry_insufficient_stock' => 'متأسفانه موجودی برخی از کالاهای این سفارش دیگر کافی نیست.',
        'returns_title' => 'مرجوعی‌های من',
        'returns_empty_title' => 'هنوز مرجوعی‌ای ثبت نشده است',
        'returns_empty_description' => 'سفارش‌های مرجوع‌شده شما اینجا نمایش داده می‌شوند.',
    ],

    'account' => [
        'reviews_coming_soon' => 'نظرات ثبت‌شده',
    ],

    'home' => [
        'row_newest' => 'جدیدترین محصولات',
        'row_popular' => 'پربازدیدترین محصولات',
    ],

    'breadcrumb' => [
        'home' => 'خانه',
        'faq' => 'سوالات متداول',
    ],

    'footer' => [
        'shop' => 'فروشگاه',
        'support' => 'پشتیبانی',
        'contact' => 'ارتباط با ما',
    ],

    // Fallbacks shown when referenced data is missing.
    'deleted_product' => 'محصول حذف‌شده',
    'guest_user' => 'کاربر',
];
