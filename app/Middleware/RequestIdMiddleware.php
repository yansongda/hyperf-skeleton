<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\RequestConstant;
use App\Util\Context;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yansongda\Supports\Str;

class RequestIdMiddleware implements MiddlewareInterface
{
    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request->hasHeader(RequestConstant::HEADER_REQUEST_ID)) {
            $request = Context::set(
                ServerRequestInterface::class,
                $request->withHeader(RequestConstant::HEADER_REQUEST_ID, Str::random(32))
            );
        }

        return $handler->handle($request);
    }
}
