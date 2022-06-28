<?php

declare(strict_types=1);

return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => 'php://stdout',
                'level' => env('APP_DEBUG', false) ? Monolog\Level::Debug : Monolog\Level::Info,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "%datetime%|%level_name%|%message% %context%\n",
                'dateFormat' => 'Y-m-d H:i:s.u',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
    'system' => [
        'handler' => [
            'class' => Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => 'php://stdout',
                'level' => env('APP_DEBUG', false) ? Monolog\Level::Debug : Monolog\Level::Info,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "%datetime%|%level_name%|%message% %context%\n",
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
                'level' => env('APP_DEBUG', false) ? Monolog\Level::Debug : Monolog\Level::Info,
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
