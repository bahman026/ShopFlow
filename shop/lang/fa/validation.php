<?php

declare(strict_types=1);

/*
 * Persian validation messages. Only the rules the storefront uses are
 * translated; anything missing falls back to the framework's English
 * (fallback_locale=en). `attributes` gives form fields human Persian names.
 */
return [
    'required' => 'وارد کردن :attribute الزامی است.',
    'string' => ':attribute باید متن باشد.',
    'integer' => ':attribute نامعتبر است.',
    'numeric' => ':attribute نامعتبر است.',
    'boolean' => ':attribute نامعتبر است.',
    'email' => ':attribute معتبر نیست.',
    'exists' => ':attribute انتخاب‌شده معتبر نیست.',
    'unique' => ':attribute قبلاً استفاده شده است.',

    'max' => [
        'numeric' => ':attribute نباید بیشتر از :max باشد.',
        'string' => ':attribute نباید بیشتر از :max نویسه باشد.',
        'array' => ':attribute نباید بیشتر از :max مورد باشد.',
        'file' => ':attribute نباید بیشتر از :max کیلوبایت باشد.',
    ],
    'min' => [
        'numeric' => ':attribute نباید کمتر از :min باشد.',
        'string' => ':attribute نباید کمتر از :min نویسه باشد.',
        'array' => ':attribute نباید کمتر از :min مورد باشد.',
        'file' => ':attribute نباید کمتر از :min کیلوبایت باشد.',
    ],
    'between' => [
        'numeric' => ':attribute خارج از محدوده مجاز است.',
        'string' => ':attribute خارج از محدوده مجاز است.',
        'array' => ':attribute خارج از محدوده مجاز است.',
        'file' => ':attribute خارج از محدوده مجاز است.',
    ],

    'attributes' => [
        'name' => 'عنوان نشانی',
        'city_id' => 'شهر',
        'address' => 'نشانی',
        'plate' => 'پلاک',
        'unit' => 'واحد',
        'postal_code' => 'کد پستی',
        'phone' => 'شماره موبایل',
        'note' => 'توضیحات',
        'latitude' => 'موقعیت مکانی',
        'longitude' => 'موقعیت مکانی',
        'first_name' => 'نام',
        'last_name' => 'نام خانوادگی',
        'email' => 'ایمیل',
        'rating' => 'امتیاز',
        'heading' => 'عنوان',
        'content' => 'متن دیدگاه',
        'count' => 'تعداد',
    ],
];
