<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Constants\RequestConstant;
use App\Exception\ApiException;
use App\Service\Security\AccessTokenService;
use App\Util\Context;
use App\Util\Logger;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiAuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected AccessTokenService $tokenService;

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
    protected function developAuthenticate(): ?array
    {
        if ('dev' === config('app.env')) {
            $oauth = [
                'user_id' => 1,
                'user_name' => '开发环境',
            ];
        }

        return $oauth ?? null;
    }

    /**
     * 内网访问直接在 header 中加 vcc-id && (内网 ip || 内网域名 || k8s service).
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function internalAuthenticate(): ?array
    {
        $host = explode(':', $this->request->getHeaderLine('host'))[0];

        if ($this->request->hasHeader(RequestConstant::HEADER_TOKEN_INTERNAL_IDENTITY)
            && is_internal_request($host)) {
            $oauth = [
                'user_id' => 1,
                'user_name' => '内网环境',
            ];
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
    protected function externalAuthenticate(): array
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
