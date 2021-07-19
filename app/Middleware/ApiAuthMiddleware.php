<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Constants\RequestConstant;
use App\Exception\ApiException;
use App\Model\UserInfo;
use App\Service\Security\AccessTokenService;
use App\Util\Logger;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiAuthMiddleware implements MiddlewareInterface
{
    protected RequestInterface $request;

    protected AccessTokenService $tokenService;

    public function __construct(RequestInterface $request, AccessTokenService $tokenService)
    {
        $this->request = $request;
        $this->tokenService = $tokenService;
    }

    /**
     * process.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \App\Exception\ApiException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        Logger::info('--> 接收到认证业务请求', [
            'url' => $this->request->fullUrl(),
            'headers' => $this->request->getHeaders(),
        ]);

        $this->testRequestSecurity();

        $oauth = $this->developAuthenticate() ?? $this->internalAuthenticate() ?? $this->externalAuthenticate();

        $request = Context::set(
            ServerRequestInterface::class,
            $this->request->withAttribute(RequestConstant::ATTRIBUTE_AUTH, $oauth)
        );

        return $handler->handle($request);
    }

    /**
     * testRequestSecurity.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \App\Exception\ApiException
     */
    protected function testRequestSecurity(): void
    {
        if ('prod' !== config('app.env')) {
            return;
        }

        foreach (RequestConstant::HEADER_TOKEN_AUTH_COOKIE as $cookieName) {
            if ($this->request->hasCookie($cookieName)) {
                return;
            }
        }

        foreach (RequestConstant::HEADER_TOKEN_AUTH_HEADER as $header) {
            if ($this->request->hasHeader($header)) {
                return;
            }
        }

        if ($this->request->hasHeader(RequestConstant::HEADER_TOKEN_INTERNAL_IDENTITY)) {
            return;
        }

        throw new ApiException(ErrorCode::AUTH_HEADER_NOT_EXIST);
    }

    /**
     * 开发环境.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function developAuthenticate(): ?UserInfo
    {
        if ('dev' === config('app.env')) {
            $oauth = (new UserInfo())
                ->setUserId(1)->setUsername('开发环境');
        }

        return $oauth ?? null;
    }

    /**
     * 内网访问直接在 header 中加 vcc-id && (内网 ip || 内网域名 || k8s service).
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function internalAuthenticate(): ?UserInfo
    {
        $host = explode(':', $this->request->getHeaderLine('host'))[0];

        if ($this->request->hasHeader(RequestConstant::HEADER_TOKEN_INTERNAL_IDENTITY) && (
                (false !== filter_var($host, FILTER_VALIDATE_IP) && (false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) || false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE))) ||
                false !== strpos($host, RequestConstant::DOMAIN_K8S_SERVICE) ||
                false !== strpos($host, RequestConstant::DOMAIN_INTERNAL)
            )) {
            $oauth = new UserInfo();
            $oauth->setUserId(intval($this->request->getHeaderLine('user-id')));
        }

        return $oauth ?? null;
    }

    /**
     * 外部网络访问需要走用户中心验证 token 有效性.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \App\Exception\ApiException
     */
    protected function externalAuthenticate(): UserInfo
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

        $accessToken = str_replace('Bearer ', '', $accessToken ?? 'none');

        return $this->tokenService->getUserInfo($accessToken);
    }
}
