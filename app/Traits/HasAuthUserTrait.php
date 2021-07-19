<?php

declare(strict_types=1);

namespace App\Traits;

use App\Constants\RequestConstant;
use App\Model\UserInfo;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Throwable;

trait HasAuthUserTrait
{
    /**
     * getAuthUser.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function getAuthUser(): ?UserInfo
    {
        $request = $this->getRequest();

        try {
            return is_null($request) ? null : $request->getAttribute(RequestConstant::ATTRIBUTE_AUTH);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Get user display name.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function getUserDisplayName(): ?string
    {
        $user = $this->getAuthUser();

        return is_null($user) ? '' : $user->getUsername();
    }

    public function getUserId(): int
    {
        $user = $this->getAuthUser();

        return is_null($user) ? 0 : $user->getUserId();
    }

    /**
     * Get request.
     *
     * @author yansongda <me@yansongda.cn>
     */
    private function getRequest(): ?RequestInterface
    {
        if (!ApplicationContext::hasContainer()) {
            return null;
        }

        return ApplicationContext::getContainer()->get(RequestInterface::class);
    }
}
