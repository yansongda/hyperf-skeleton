<?php

declare(strict_types=1);

use Hyperf\Server\CoroutineServer;
use Hyperf\Server\Event;
use Hyperf\Server\ServerInterface;
use Swoole\Constant;

return [
    'type' => CoroutineServer::class,
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
        Constant::OPTION_PID_FILE => BASE_PATH.'/runtime/app.pid',
        Constant::OPTION_PACKAGE_MAX_LENGTH => 5 * 1024 * 1024,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
    ],
];
