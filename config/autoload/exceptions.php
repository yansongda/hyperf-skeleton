<?php

declare(strict_types=1);

use App\Exception\Handler\ApiExceptionHandler;
use App\Exception\Handler\HttpExceptionHandler;
use App\Exception\Handler\UnknownExceptionHandler;
use App\Exception\Handler\ValidationExceptionHandler;

return [
    'handler' => [
        'http' => [
            HttpExceptionHandler::class,
            ValidationExceptionHandler::class,
            ApiExceptionHandler::class,
            UnknownExceptionHandler::class,
        ],
    ],
];
