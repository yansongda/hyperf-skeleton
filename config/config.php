<?php

declare(strict_types=1);

return [
    'scan_cacheable' => env('SCAN_CACHEABLE', false),

    'app' => [
        'name' => env('APP_NAME', 'template'),
        'env' => env('APP_ENV', 'prod'),
        'url' => env('APP_URL', 'https://yansongda.cn'),
    ],

    'account' => [
        'url' => env('ACCOUNT_URL', 'https://yansongda.cn'),
    ],
];
