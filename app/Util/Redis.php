<?php

declare(strict_types=1);

namespace App\Util;

use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

class Redis
{
    public static function get(string $config = 'default'): RedisProxy
    {
        return ApplicationContext::getContainer()->get(RedisFactory::class)->get($config);
    }
}
