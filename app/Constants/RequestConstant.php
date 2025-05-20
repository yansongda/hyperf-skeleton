<?php

declare(strict_types=1);

namespace App\Constants;

class RequestConstant
{
    /**
     * 请求 ID.
     */
    public const string HEADER_REQUEST_ID = 'x-request-id';

    /**
     * 外部请求认证 header.
     */
    public const array HEADER_TOKEN_AUTH_HEADER = ['Authorization'];

    /**
     * 外部请求认证 cookie.
     */
    public const array HEADER_TOKEN_AUTH_COOKIE = [
        'access_token', 'Authorization',
    ];

    /**
     * 内部请求 header 识别.
     */
    public const string HEADER_TOKEN_INTERNAL_IDENTITY = 'yansongda';

    /**
     * 内网域名识别.
     */
    public const array DOMAIN_INTERNAL = [
        'yansongda-app.yansongda-prod:8080', 'internal.yansongda.cn',
    ];

    /**
     * 认证后 request attribute 中存储的字段.
     */
    public const string ATTRIBUTE_AUTH_USERINFO = 'oauth-userinfo';

    /**
     * 认证后 request attribute 中存储的字段.
     */
    public const string ATTRIBUTE_AUTH_VERIFYINFO = 'oauth-verifyinfo';
}
