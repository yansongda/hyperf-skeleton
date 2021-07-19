<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Constants\RequestConstant;
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
        $host = explode(':', $request->getHeaderLine('host'))[0];

        // 内网域名 || k8s service
        if ((false !== filter_var($host, FILTER_VALIDATE_IP) && (false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) || false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE))) ||
            false !== strpos($host, RequestConstant::DOMAIN_K8S_SERVICE) ||
            false !== strpos($host, RequestConstant::DOMAIN_INTERNAL)
        ) {
            return $handler->handle($request);
        }

        throw new ApiException(ErrorCode::NO_PERMISSION);
    }
}
