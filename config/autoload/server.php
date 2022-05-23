<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\ServerInterface;

return [
    'mode' => SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'http',
            'type' => ServerInterface::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 8080,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
    'settings' => [
        'enable_coroutine' => true,
        'worker_num' => 2 * swoole_cpu_num(),
        'reactor_num' => 2 * swoole_cpu_num(),
        'pid_file' => BASE_PATH.'/runtime/app.pid',
        'open_tcp_nodelay' => true,
        'max_coroutine' => 100000,
        'open_http2_protocol' => true,
        // 'max_request' => 100000,
        'input_buffer_size' => 2 * 1024 * 1024,
        'output_buffer_size' => 2 * 1024 * 1024,
        'socket_buffer_size' => 2 * 1024 * 1024,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
    ],
];
