<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default admin account
    |--------------------------------------------------------------------------
    |
    | Credentials used by the AdminSeeder to create the initial super admin
    | account. Override these via the matching ADMIN_* environment variables.
    |
    */
    'account' => [
        'first_name' => env('ADMIN_FIRST_NAME', 'john'),
        'last_name' => env('ADMIN_LAST_NAME', 'doe'),
        'email' => env('ADMIN_EMAIL', 'admin@shopFlow.dev'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],
];
