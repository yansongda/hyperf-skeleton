<?php

declare(strict_types=1);

return [
    Hyperf\Contract\StdoutLoggerInterface::class => App\Util\Logger::class,
    Hyperf\HttpServer\CoreMiddleware::class => App\Middleware\CoreMiddleware::class,
];
