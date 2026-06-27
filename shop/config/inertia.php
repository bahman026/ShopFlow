<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Page components
    |--------------------------------------------------------------------------
    |
    | This storefront keeps its Inertia pages in `resources/js/Pages` (capital
    | "P"), matching the `resolve()` glob in `resources/js/app.js`. Inertia's
    | default is lowercase `pages`, so we override the path here; otherwise the
    | case-sensitive page-existence check in tests fails on Linux.
    |
    */

    'pages' => [

        'ensure_pages_exist' => false,

        'paths' => [
            resource_path('js/Pages'),
        ],

        'extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],

    ],

    'testing' => [

        'ensure_pages_exist' => true,

    ],

];
