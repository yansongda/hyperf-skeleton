<?php

declare(strict_types=1);

return [
    'default' => [
        'handler' => [
            'class' => Yansongda\Supports\Logger\StdoutHandler::class,
            'constructor' => [
                'level' => env('APP_DEBUG', false) ? Monolog\Logger::DEBUG : Monolog\Logger::INFO,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => '[%datetime%] %channel%.%level_name%: %message% %context%',
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
    'system' => [
        'handler' => [
            'class' => Yansongda\Supports\Logger\StdoutHandler::class,
            'constructor' => [
                'level' => Monolog\Logger::WARNING,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => '[%datetime%] %channel%.%level_name%: %message% %context%',
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
    'file' => [
        'handler' => [
            'class' => Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH.'/runtime/logs/yansongda.log',
                'level' => env('APP_DEBUG', false) ? Monolog\Logger::DEBUG : Monolog\Logger::INFO,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message% %context%\n",
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
];
