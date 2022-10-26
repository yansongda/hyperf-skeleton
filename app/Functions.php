<?php

declare(strict_types=1);

use App\Constants\RequestConstant;
use App\Util\Context;
use App\Util\Logger;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Psr\Http\Message\ServerRequestInterface;

if (!function_exists('get_request_id')) {
    /**
     * 获取当前协程内的 request_id.
     */
    function get_request_id(): string
    {
        $request = Context::get(ServerRequestInterface::class);

        if (!is_null($request)) {
            return $request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID);
        }

        return Context::getOrSet(RequestConstant::HEADER_REQUEST_ID, uniqid());
    }
}

if (!function_exists('is_internal_host')) {
    function is_internal_host(string $host): bool
    {
        $hostIp = explode(':', $host)[0] ?? '';

        return (false !== filter_var($hostIp, FILTER_VALIDATE_IP) && (false === filter_var($hostIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) || false === filter_var($hostIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)))
            || str_contains(RequestConstant::DOMAIN_K8S_SERVICE, $host)
            || str_contains(RequestConstant::DOMAIN_INTERNAL, $host);
    }
}

if (!function_exists('do_parallel')) {
    function do_parallel(array $calls, int $concurrent = 30, bool $throw = false): array
    {
        try {
            $results = parallel($calls, $concurrent);
        } catch (ParallelExecutionException $e) {
            if ($throw) {
                throw $e;
            }

            $throwable = $e->getThrowables();
            $results = $e->getResults();

            foreach ($throwable as $key => $throw) {
                /* @var \Throwable $throw */
                Logger::error('[Functions] 并发执行失败，已忽略失败部分', ['key' => $key, 'message' => $throw->getMessage(), 'trace' => $throw->getTrace()]);
            }
        }

        return $results ?? [];
    }
}

if (!function_exists('to_array')) {
    /**
     * 将 string 转换成 array.
     */
    function to_array(array|string|null $value): array
    {
        if (is_string($value)) {
            $value = explode(',', str_replace('，', ',', $value));
        }

        if (is_array($value)) {
            return array_filter($value, function ($value) {
                return !is_null($value) && '' !== $value;
            });
        }

        return (array) $value;
    }
}

if (!function_exists('to_bool')) {
    function to_bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ('false' === $value) {
            return false;
        }

        if ('true' === $value) {
            return true;
        }

        return boolval($value);
    }
}
