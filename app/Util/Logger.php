<?php

declare(strict_types=1);

namespace App\Util;

use Hyperf\Context\ApplicationContext;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * @method static void emergency($message, array $context = [], array $logger = [])
 * @method static void alert($message, array $context = [], array $logger = [])
 * @method static void critical($message, array $context = [], array $logger = [])
 * @method static void error($message, array $context = [], array $logger = [])
 * @method static void warning($message, array $context = [], array $logger = [])
 * @method static void notice($message, array $context = [], array $logger = [])
 * @method static void info($message, array $context = [], array $logger = [])
 * @method static void debug($message, array $context = [], array $logger = [])
 * @method static void log($message, array $context = [], array $logger = [])
 */
class Logger
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function __callStatic(string $method, array $params): void
    {
        $channel = $params[2][0] ?? $params[2]['channel'] ?? 'app';
        $config = $params[2][1] ?? $params[2]['config'] ?? 'default';
        $logger = Logger::get($channel, $config);

        if (method_exists($logger, $method)) {
            $logger->{$method}($params[0] ?? '', $params[1] ?? []);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        return Logger::get('sys', 'system');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function get(string $name = 'app', string $config = 'default'): LoggerInterface
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name, $config);
    }
}
