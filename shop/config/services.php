<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Neshan maps. The web map key is sent to the browser (domain-restricted);
    // the service key stays server-side for reverse geocoding.
    'neshan' => [
        'map_key' => env('NESHAN_MAP_KEY'),
        'service_key' => env('NESHAN_SERVICE_KEY'),
    ],

    // Zarinpal payment gateway. base_url defaults to the sandbox so local/CI
    // never accidentally hits production; any 36-character merchant_id works
    // in sandbox mode (no real merchant account needed).
    // sms.ir, used for login/password-reset OTP over the `send/verify`
    // endpoint (shared service line — no rented number needed). Leave
    // api_key empty and the app logs the code instead of sending it, so local
    // and CI need no credentials and spend no credit. template_id is the
    // approved template from the sms.ir panel.
    'sms_ir' => [
        'api_key' => env('SMS_IR_API_KEY'),
        'template_id' => env('SMS_IR_TEMPLATE_ID'),
        'base_url' => env('SMS_IR_BASE_URL', 'https://api.sms.ir'),
    ],

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'base_url' => env('ZARINPAL_BASE_URL', 'https://sandbox.zarinpal.com'),
    ],

];
