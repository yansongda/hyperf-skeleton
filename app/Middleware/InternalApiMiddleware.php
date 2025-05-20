<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Exception\ApiException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function App\is_internal_request;

class InternalApiMiddleware implements MiddlewareInterface
{
    /**
     * @throws ApiException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (is_internal_request($request->getHeaderLine('host'))) {
            return $handler->handle($request);
        }

        throw new ApiException(ErrorCode::AUTH_NO_PERMISSION);
    }
}
