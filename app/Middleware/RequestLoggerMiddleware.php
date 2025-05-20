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
        if (str_contains($request->getHeaderLine('user-agent'), 'kube-probe')) {
            return $handler->handle($request);
        }

        Logger::info(
            '--> 处理业务请求',
            [
                'url' => $request->fullUrl(),
                'inputs' => $request->getParsedBody(),
                'header' => $request->getHeaders(),
            ]
        );

        $startTime = microtime(true);

        try {
            $response = $handler->handle($request);

            $elapsed = microtime(true) - $startTime;
        } catch (Throwable $e) {
            $elapsed = microtime(true) - $startTime;

            if ($e instanceof ApiException) {
                $this->metric($request->fullUrl(), $elapsed, $e->getCode(), ['message' => $e->getMessage(), 'raw' => $e->raw]);
            }

            throw $e;
        }

        $this->metric($request->fullUrl(), $elapsed, 0, ['response' => str_contains($response->getHeaderLine('content-type'), 'application/json') ? (string) $response->getBody() : '非 json 响应']);

        return $response;
    }

    protected function metric(string $url, float $elapsed, int $code = 0, array $extra = []): void
    {
        $this->eventDispatcher->dispatch(new Metric(new Request([
            'code' => $code,
            'url' => $url,
            'duration' => $elapsed,
            'extra' => $extra,
        ])));
    }
}
