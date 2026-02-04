<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Event\Metric;
use App\Exception\ApiException;
use App\Model\Metric\Request;
use App\Util\Logger;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class RequestLoggerMiddleware implements MiddlewareInterface
{
    public function __construct(protected EventDispatcherInterface $eventDispatcher) {}

    /**
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var \Hyperf\HttpMessage\Server\Request $request */
        if (str_contains($request->getHeaderLine('user-agent'), 'kube-probe') || 'OPTIONS' == $request->getMethod()) {
            return $handler->handle($request);
        }

        Logger::info(
            '--> 处理业务请求',
            [
                'url' => $request->fullUrl(),
                'method' => $request->getMethod(),
                'inputs' => $request->getParsedBody(),
                'headers' => $request->getHeaders(),
                'cookies' => $request->getCookieParams(),
            ]
        );

        $startTime = microtime(true);

        try {
            $response = $handler->handle($request);

            $elapsed = microtime(true) - $startTime;

            Logger::info('<-- 处理业务请求完毕', ['time' => $elapsed, 'response' => $this->getLogResponse($response)]);
        } catch (Throwable $e) {
            $elapsed = microtime(true) - $startTime;

            if ($e instanceof ApiException) {
                $this->metric($request->fullUrl(), $elapsed, $e->getCode());

                Logger::info('<-- 业务处理被中断', ['time' => $elapsed, 'code' => $e->getCode(), 'message' => $e->getMessage()]);
            }

            throw $e;
        }

        $this->metric($request->fullUrl(), $elapsed);

        return $response;
    }

    protected function getLogResponse(ResponseInterface $response): string
    {
        if (!str_contains($response->getHeaderLine('content-type'), 'application/json')) {
            return '非 json 响应';
        }

        $body = (string) $response->getBody();

        // 10K
        if (strlen($body) <= 10 * 1024) {
            return $body;
        }

        return '[仅展示前1024个字符]'.substr($body, 0, 1024).'...';
    }

    protected function metric(string $url, float $elapsed, int $code = 0): void
    {
        $this->eventDispatcher->dispatch(new Metric(new Request([
            'code' => $code,
            'url' => $url,
            'duration' => $elapsed,
        ])));
    }
}
