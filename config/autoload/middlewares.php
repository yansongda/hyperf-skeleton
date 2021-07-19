<?php

declare(strict_types=1);

return [
    'http' => [
        App\Middleware\RequestIdMiddleware::class,
        App\Middleware\CorsMiddleware::class,
        Hyperf\Validation\Middleware\ValidationMiddleware::class,
    ],
];
