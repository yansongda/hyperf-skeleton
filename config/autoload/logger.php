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
                'format' => '%datetime%|%level_name%|%message% %context%',
                'dateFormat' => 'Y-m-d H:i:s.u',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
    'system' => [
        'handler' => [
            'class' => Yansongda\Supports\Logger\StdoutHandler::class,
            'constructor' => [
                'level' => env('APP_DEBUG', false) ? Monolog\Logger::DEBUG : Monolog\Logger::INFO,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => '%datetime%|%level_name%|%message% %context%',
                'dateFormat' => 'Y-m-d H:i:s.u',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
    'file' => [
        'handler' => [
            'class' => Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH.'/runtime/logs/app.log',
                'level' => env('APP_DEBUG', false) ? Monolog\Logger::DEBUG : Monolog\Logger::INFO,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message% %context%\n",
                'dateFormat' => 'Y-m-d H:i:s.u',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
];
