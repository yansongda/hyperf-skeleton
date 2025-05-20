<?php

declare(strict_types=1);

namespace App\Model\OAuth2;

use App\Model\AbstractModel;

class VerifyInfo extends AbstractModel
{
    private int $userId = 0;

    private string $accessToken = '';

    private string $clientId = '';

    private string $expiredAt = '';

    private int $expires = 0;

    private string $scope = '';

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int|string $userId): void
    {
        $this->userId = intval($userId);
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getExpiredAt(): string
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(string $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }

    public function getExpires(): int
    {
        return $this->expires;
    }

    public function setExpires(int|string $expires): void
    {
        $this->expires = intval($expires);
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }
}
