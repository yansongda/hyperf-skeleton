<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH'),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'options' => [
            Redis::OPT_PREFIX => env('REDIS_PREFIX', ''),
            Redis::OPT_SERIALIZER => (string) Redis::SERIALIZER_NONE,
            Redis::OPT_SCAN => (string) Redis::SCAN_RETRY,
        ],
        'pool' => [
            'min_connections' => 3,
            'max_connections' => (int) env('REDIS_POOL_MAX_CONNECTIONS', 30),
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 600),
        ],
    ],
];
