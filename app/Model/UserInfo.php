<?php

declare(strict_types=1);

namespace App\Model;

class UserInfo extends AbstractModel
{
    private ?int $userId = null;

    private ?string $username = null;

    private ?string $scope = null;

    private ?string $accessToken = null;

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param string|int|null $userId
     */
    public function setUserId($userId): UserInfo
    {
        $this->userId = is_null($userId) ? null : intval($userId);

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): UserInfo
    {
        $this->username = $username;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): UserInfo
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): UserInfo
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
