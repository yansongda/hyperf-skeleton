<?php

declare(strict_types=1);

namespace App\Fallback;

use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Util\Logger;
use Throwable;

class HttpServiceFallback
{
    /**
     * 降级处理.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \App\Exception\ApiException
     */
    public function accountRequestApi(string $method, string $endpoint, array $options, ?Throwable $throwable): void
    {
        Logger::error(
            '[HttpServiceFallback][Account] 重试 2 次后请求用户中心出现异常或超时，上帝也拯救不了后续流程了，赶紧检查处理',
            func_get_args()
        );

        throw new ApiException(ErrorCode::ACCOUNT_ERROR);
    }
}
