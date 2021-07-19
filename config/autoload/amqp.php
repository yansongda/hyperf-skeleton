<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => env('AMQP_HOST', 'localhost'),
        'port' => (int) env('AMQP_PORT', 5672),
        'user' => env('AMQP_USER', 'guest'),
        'password' => env('AMQP_PASSWORD', 'guest'),
        'vhost' => env('AMQP_VHOST', '/'),
        'concurrent' => [
            'limit' => 1,
        ],
        'pool' => [
            // 同时开启的连接数
            // 因为新版本连接是支持多路复用的，所以可以用极少的连接数达到很高的并发
            'connections' => 10,
        ],
        'params' => [
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => 3,
            'read_write_timeout' => 6,
            'context' => null,
            'keepalive' => true,
            'heartbeat' => 3,
            'channel_rpc_timeout' => 0.0,
            'close_on_destruct' => false,
            // 多路复用中闲置 Channel 的最大值，超过这个数量后，会关闭多余的限制 Channel
            'max_idle_channels' => 10,
        ],
    ],
];
