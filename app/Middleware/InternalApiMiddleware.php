<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Exception\ApiException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InternalApiMiddleware implements MiddlewareInterface
{
    /**
     * @throws \App\Exception\ApiException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (is_internal_host($request->getHeaderLine('host'))) {
            return $handler->handle($request);
        }

        throw new ApiException(ErrorCode::NO_PERMISSION);
    }
}
