<?php

declare(strict_types=1);

return [
    'http' => [
        App\Middleware\RequestIdMiddleware::class,
        App\Middleware\RequestLoggerMiddleware::class,
        App\Middleware\CorsMiddleware::class,
        // App\Middleware\AcceptLanguageMiddleware::class,
        Hyperf\Validation\Middleware\ValidationMiddleware::class,
    ],
];
