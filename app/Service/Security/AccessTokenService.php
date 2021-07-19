<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Constants\ErrorCode;
use App\Constants\Security\AccessTokenConstant;
use App\Exception\ApiException;
use App\Model\UserInfo;
use App\Service\Http\AccountHttp;
use Hyperf\Di\Annotation\Inject;

class AccessTokenService
{
    /**
     * @Inject
     */
    protected AccountHttp $accountHttp;

    /**
     * 获取 userinfo 信息.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \App\Exception\ApiException
     */
    public function getUserInfo(string $accessToken): UserInfo
    {
        $info = $this->accountHttp->getUserInfo($accessToken);

        $data = new UserInfo();
        $data->unserialize(json_encode($info));

        if (false === strpos($data->get('scope'), AccessTokenConstant::SCOPE)) {
            throw new ApiException(ErrorCode::NO_PERMISSION);
        }

        return $data;
    }
}
