<?php

declare(strict_types=1);

namespace App\Fallback;

use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Util\Logger;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpServiceFallback
{
    /**
     * @throws ApiException
     */
    public function generalRequest(string $method, string $endpoint, array $options, ?Throwable $throwable): ResponseInterface
    {
        Logger::warning(
            '[HttpServiceFallback][general] 尝试 2 次后请求第三方接口出现异常或超时，请检查',
            func_get_args()
        );

        throw new ApiException(ErrorCode::THIRD_API_ERROR, $throwable?->getMessage() ?? null);
    }
}
