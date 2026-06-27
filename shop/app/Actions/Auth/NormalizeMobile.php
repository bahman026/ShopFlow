<?php

declare(strict_types=1);

namespace App\Actions\Auth;

class NormalizeMobile
{
    /**
     * Normalise a user-entered mobile number to the canonical `09xxxxxxxxx`
     * form, converting Persian/Arabic digits and common prefixes. Returns null
     * when the value is not a valid Iranian mobile number.
     */
    public function __invoke(string $mobile): ?string
    {
        $digits = strtr($mobile, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $digits = preg_replace('/\D+/', '', $digits) ?? '';

        // Accept 98xxxxxxxxxx and 0098xxxxxxxxxx variants too.
        if (str_starts_with($digits, '0098')) {
            $digits = '0'.substr($digits, 4);
        } elseif (str_starts_with($digits, '98') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0'.$digits;
        }

        return preg_match('/^09\d{9}$/', $digits) === 1 ? $digits : null;
    }
}
