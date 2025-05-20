<?php

declare(strict_types=1);

namespace App;

use App\Constants\RequestConstant;
use App\Model\OAuth2\VerifyInfo;
use App\Util\Context;
use App\Util\Ip;
use App\Util\Logger;
use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function Hyperf\Config\config;
use function Hyperf\Coroutine\parallel;

function get_request_id(): string
{
    /** @var null|RequestInterface $request */
    $request = Context::get(ServerRequestInterface::class);

    if (!is_null($request)) {
        return $request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID);
    }

    return Context::getOrSet(RequestConstant::HEADER_REQUEST_ID, uniqid());
}

function is_internal_request(string $host): bool
{
    $host = explode(':', $host)[0];

    return (false !== filter_var($host, FILTER_VALIDATE_IP) && (false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) || false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)))
        || in_array($host, RequestConstant::DOMAIN_INTERNAL);
}

function get_current_oauth_verifyinfo(): ?VerifyInfo
{
    $request = Context::get(ServerRequestInterface::class);

    return is_null($request) ? null : $request->getAttribute(RequestConstant::ATTRIBUTE_AUTH_VERIFYINFO);
}

function get_request_headers(string $name, bool $line = false): array|string
{
    /** @var null|ServerRequestInterface $request */
    $request = Context::get(ServerRequestInterface::class);

    if ($line) {
        return $request?->getHeaderLine($name) ?? '';
    }

    foreach ($request?->getHeader($name) ?? [] as $item) {
        $result = array_merge(
            $result ?? [],
            array_map(static fn ($v) => trim($v), explode(',', $item))
        );
    }

    return $result ?? [];
}

function get_request_client_ip(?array $ips = null): string
{
    $ips = $ips ?? config('proxy_ips', []);

    foreach (get_request_headers('x-forwarded-for') as $ip) {
        if (false === Ip::inIps($ip, $ips)) {
            return $ip;
        }
    }

    return '';
}

function to_array(null|array|string $value): array
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

function any_in_array(array $search, array $haystack, bool $strict = false): bool
{
    foreach ($search as $item) {
        if (in_array($item, $haystack, $strict)) {
            return true;
        }
    }

    return false;
}

function do_parallel(array $calls, int $concurrent = 30, bool $isThrow = false, bool $isLog = true): array
{
    try {
        $results = parallel($calls, $concurrent);
    } catch (ParallelExecutionException $e) {
        $throwable = $e->getThrowables();
        $results = $e->getResults();

        if ($isLog) {
            foreach ($throwable as $key => $throw) {
                /* @var Throwable $throw */
                Logger::error('[Functions] 并发执行失败'.($throw ? '' : '，已忽略失败部分').'，请确认检查', ['key' => $key, 'message' => $throw->getMessage(), 'trace' => $throw->getTraceAsString()]);
            }
        }

        if ($isThrow) {
            throw $e;
        }
    }

    return $results;
}
