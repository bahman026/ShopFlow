<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Fixed login code — TESTING ONLY
    |--------------------------------------------------------------------------
    |
    | When `fixed_code` is set, the listed mobiles get that code instead of a
    | random one and no SMS is sent. It exists for the window where sms.ir is
    | still on a sandbox key, which accepts a send and delivers nothing, so
    | nobody could otherwise complete a login.
    |
    | This is an authentication bypass. Anyone who knows a listed mobile and
    | the code can sign in as that person, and the storefront registers an
    | account on first login. Leave `fixed_code` EMPTY the moment real SMS
    | works, and never let a shop with customers run with it set.
    |
    | `fixed_mobiles` is what keeps it survivable: only those numbers bypass,
    | so a stranger typing their own number still gets a real code they never
    | receive. Leaving it empty applies the code to EVERY number — a bypass for
    | the whole site — so that case is logged as critical on every use.
    |
    */

    'fixed_code' => env('OTP_FIXED_CODE'),

    /**
     * Comma-separated mobiles the fixed code applies to.
     */
    'fixed_mobiles' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OTP_FIXED_MOBILES', '')),
    ))),

];
