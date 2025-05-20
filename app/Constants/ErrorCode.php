<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

/**
 * @method static string getMessage($code, array $params = [])
 */
#[Constants]
enum ErrorCode: int
{
    use EnumConstantsTrait;

    #[Message('未知错误')]
    case UNKNOWN_ERROR = 9999;

    /**
     * 系统业务错误.
     */
    #[Message('认证失败：Authorization 头不存在')]
    case AUTH_HEADER_NOT_EXIST = 1000;

    #[Message('认证失败：token 非法')]
    case AUTH_FAILED = 1001;

    #[Message('认证失败：权限不足')]
    case AUTH_NO_PERMISSION = 1002;

    /**
     * 客户端参数错误.
     */
    #[Message('参数错误，请参考 API 文档')]
    case PARAMS_INVALID = 1100;

    #[Message('参数错误：没有符合条件的数据')]
    case PARAMS_DATA_NOT_FOUND = 1101;

    /**
     * 第三方 API 错误.
     */
    #[Message('内部错误, 请联系管理员')]
    case THIRD_API_ERROR = 5000;

    #[Message('内部错误, 请联系管理员')]
    case OAUTH2_ERROR = 5001;

    /**
     * 内部参数错误.
     */
    #[Message('内部错误, 请联系管理员')]
    case INTERNAL_PARAMS_ERROR = 9900;

    #[Message('内部错误，请重试或联系管理员')]
    case INTERNAL_MUTEX_LOCKER_ERROR = 9901;
}
