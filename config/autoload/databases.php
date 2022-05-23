<?php

declare(strict_types=1);

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'yansongda'),
        'port' => env('DB_PORT', 3306),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', 'root'),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 3,
            'max_connections' => (int) env('DB_POOL_MAX_CONNECTIONS', 50),
            'connect_timeout' => 1.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 600),
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model/Entity',
                'force_casts' => true,
                'inheritance' => 'AbstractEntity',
            ],
        ],
    ],
];
