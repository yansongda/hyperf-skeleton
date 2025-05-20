<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\ApiException;
use App\Model\OAuth2\VerifyInfo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiAuthVerifyInfoMiddleware extends AbstractApiAuthMiddleware implements MiddlewareInterface
{
    /**
     * @throws ApiException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handle($handler);
    }

    protected function getDevelopAuthorization(): VerifyInfo
    {
        return new VerifyInfo([
            'user_id' => '1',
        ]);
    }

    protected function getInternalAuthorization(): VerifyInfo
    {
        return new VerifyInfo([
            'user_id' => 1,
        ]);
    }

    /**
     * @throws ApiException
     */
    protected function getExternalAuthorization(string $accessToken): VerifyInfo
    {
        return $this->tokenService->getVerifyInfo($accessToken);
    }
}
