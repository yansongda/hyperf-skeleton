<?php

declare(strict_types=1);

namespace App\Util;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Guzzle\HandlerStackFactory;

use function Hyperf\Support\make;

class Http
{
    public static function createPool(array $options = [], array $poolOptions = [], array $middlewares = []): Client
    {
        return make(Client::class, [
            'config' => array_merge(
                [
                    'handler' => (new HandlerStackFactory())->create(array_merge([
                        'min_connections' => 5,
                        'max_connections' => 100,
                        'wait_timeout' => 3.0,
                        'max_idle_time' => 30,
                    ], $poolOptions), $middlewares),
                    'timeout' => 1.0,
                    'headers' => [
                        'User-Agent' => 'yansongda/hyperf-skeleton',
                        'X-author' => ['yansongda <me@yansongda.cn>'],
                    ],
                ],
                $options,
            ),
        ]);
    }

    public static function create(array $options = []): Client
    {
        return make(Client::class, [
            'config' => array_merge(
                [
                    'handler' => HandlerStack::create(new CoroutineHandler()),
                    'timeout' => 5.0,
                    'headers' => [
                        'User-Agent' => 'yansongda/hyperf-skeleton',
                        'X-author' => ['yansongda <me@yansongda.cn>'],
                    ],
                ],
                $options,
            ),
        ]);
    }
}
