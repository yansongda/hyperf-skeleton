<?php

declare(strict_types=1);

namespace App\Util;

use App\Exception\MutexLockerException;
use Hyperf\Retry\FlatStrategy;
use Hyperf\Retry\Policy\ClassifierRetryPolicy;
use Hyperf\Retry\Policy\MaxAttemptsRetryPolicy;
use Hyperf\Retry\Policy\SleepRetryPolicy;
use Hyperf\Retry\Retry;
use RedisException;
use Throwable;

class MutexLocker
{
    /**
     * @param int      $ttl   占锁的超时时间
     * @param int      $tries 尝试次数
     * @param null|int $sleep 每次等待时间，参数为 null 时，每次的等待时间 = ttl/tries
     */
    public static function try(string $key, int $ttl = 60, int $tries = 3, ?int $sleep = null): bool
    {
        try {
            $sleep = !is_null($sleep) ? $sleep * 1000 : intval($ttl / $tries) * 1000;

            return
                Retry::with(
                    new MaxAttemptsRetryPolicy($tries + 1),
                    new SleepRetryPolicy($sleep, FlatStrategy::class),
                    new ClassifierRetryPolicy()
                )->call(function () use ($key, $ttl) {
                    if (self::lock($key, $ttl)) {
                        return true;
                    }

                    throw new MutexLockerException();
                });
        } catch (Throwable) {
        }

        return false;
    }

    public static function lock(string $key, int $ttl = 60): bool
    {
        try {
            return boolval(Redis::get()->set($key, '1', ['NX', 'EX' => $ttl]));
        } catch (RedisException $e) {
            Logger::error('[MutexLocker] 加锁操作 Redis 失败，请检查 Redis 状态', ['message' => $e->getMessage(), 'key' => $key, 'ttl' => $ttl]);
        }

        return false;
    }

    public static function release(string $key): bool
    {
        try {
            return boolval(Redis::get()->del($key));
        } catch (RedisException $e) {
            Logger::error('[MutexLocker] 释放锁操作 Redis 失败，请检查 Redis 状态', ['message' => $e->getMessage(), 'key' => $key]);
        }

        return false;
    }

    /**
     * @throws MutexLockerException
     */
    public static function retry(callable $operation, int $retries = 3, int $delay = 2): mixed
    {
        try {
            return Retry::with(
                new MaxAttemptsRetryPolicy($retries + 1),
                new SleepRetryPolicy($delay * 1000, FlatStrategy::class),
                new ClassifierRetryPolicy()
            )->call($operation);
        } catch (Throwable) {
        }

        throw new MutexLockerException();
    }
}
