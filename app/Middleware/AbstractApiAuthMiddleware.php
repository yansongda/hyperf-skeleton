<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Constants\RequestConstant;
use App\Exception\ApiException;
use App\Model\AbstractModel;
use App\Service\AccessTokenService;
use App\Util\Context;
use App\Util\Logger;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function App\is_internal_request;
use function Hyperf\Config\config;

abstract class AbstractApiAuthMiddleware
{
    public function __construct(
        protected RequestInterface $request,
        protected AccessTokenService $tokenService
    ) {}

    /**
     * @throws ApiException
     */
    public function handle(RequestHandlerInterface $handler): ResponseInterface
    {
        Logger::info('--> 接收到认证业务请求', [
            'url' => $this->request->fullUrl(),
            'headers' => $this->request->getHeaders(),
        ]);

        $this->testRequestSecurity();

        $oauth = $this->developAuthorization() ?? $this->internalAuthorization() ?? $this->externalAuthorization();

        $request = Context::set(
            ServerRequestInterface::class,
            $this->request->withAttribute(RequestConstant::ATTRIBUTE_AUTH_VERIFYINFO, $oauth)
        );

        return $handler->handle($request);
    }

    /**
     * @throws ApiException
     */
    protected function testRequestSecurity(): void
    {
        if ('prod' !== config('app.env')) {
            return;
        }

        foreach (RequestConstant::HEADER_TOKEN_AUTH_HEADER as $header) {
            if ($this->request->hasHeader($header)) {
                return;
            }
        }

        if ($this->request->hasHeader(RequestConstant::HEADER_TOKEN_INTERNAL_IDENTITY)) {
            return;
        }

        foreach (RequestConstant::HEADER_TOKEN_AUTH_COOKIE as $cookieName) {
            if ($this->request->hasCookie($cookieName)) {
                return;
            }
        }

        throw new ApiException(ErrorCode::AUTH_HEADER_NOT_EXIST);
    }

    /**
     * 开发环境.
     */
    protected function developAuthorization(): ?AbstractModel
    {
        if ('dev' === config('app.env')) {
            $oauth = $this->getDevelopAuthorization();
        }

        return $oauth ?? null;
    }

    /**
     * 内网访问直接在 header 中加 vcc-id && (内网 ip || 内网域名 || k8s service).
     */
    protected function internalAuthorization(): ?AbstractModel
    {
        if ($this->request->hasHeader(RequestConstant::HEADER_TOKEN_INTERNAL_IDENTITY)
            && is_internal_request($this->request->getHeaderLine('host'))) {
            $oauth = $this->getInternalAuthorization();
        }

        return $oauth ?? null;
    }

    /**
     * 外部网络访问需要走用户中心验证 token 有效性.
     */
    protected function externalAuthorization(): AbstractModel
    {
        foreach (RequestConstant::HEADER_TOKEN_AUTH_HEADER as $header) {
            if ($this->request->hasHeader($header)) {
                $accessToken = $this->request->getHeaderLine($header);

                break;
            }
        }

        if (empty($accessToken)) {
            foreach (RequestConstant::HEADER_TOKEN_AUTH_COOKIE as $cookieName) {
                if ($this->request->hasCookie($cookieName)) {
                    $accessToken = $this->request->cookie($cookieName);

                    break;
                }
            }
        }

        return $this->getExternalAuthorization($accessToken ?? 'none');
    }

    abstract protected function getDevelopAuthorization(): AbstractModel;

    abstract protected function getInternalAuthorization(): AbstractModel;

    abstract protected function getExternalAuthorization(string $accessToken): AbstractModel;
}
