<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiException;
use App\Model\OAuth2\VerifyInfo;
use App\Service\Http\OAuth2Http;
use Hyperf\Di\Annotation\Inject;

class AccessTokenService
{
    #[Inject]
    protected OAuth2Http $oauth2Http;

    /**
     * @throws ApiException
     */
    public function getVerifyInfo(string $accessToken): VerifyInfo
    {
        return $this->oauth2Http->getVerifyInfo($accessToken);
    }
}
