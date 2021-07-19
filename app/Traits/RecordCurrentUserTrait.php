<?php

declare(strict_types=1);

namespace App\Traits;

use App\Model\Entity\AbstractEntity;

/**
 * 需要记录创建人/更新人.
 */
trait RecordCurrentUserTrait
{
    use HasAuthUserTrait;

    /**
     * creating.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function recordCreateUser(AbstractEntity $model): void
    {
        if (false === $model->timestamps) {
            return;
        }

        if (!is_null($model::CREATED_AT) && !is_null($model::PREFIX_CREATED_USER)) {
            $model->setAttribute($model::PREFIX_CREATED_USER.'id', $this->getUserId());
            $model->setAttribute($model::PREFIX_CREATED_USER.'name', $this->getUserDisplayName());
        }

        if (!is_null($model::UPDATED_AT) && !is_null($model::PREFIX_UPDATED_USER)) {
            $model->setAttribute($model::PREFIX_UPDATED_USER.'id', $this->getUserId());
            $model->setAttribute($model::PREFIX_UPDATED_USER.'name', $this->getUserDisplayName());
        }
    }

    /**
     * updating.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function recordUpdateUser(AbstractEntity $model): void
    {
        if (false === $model->timestamps) {
            return;
        }

        if (!is_null($model::UPDATED_AT) && !is_null($model::PREFIX_UPDATED_USER)) {
            $model->setAttribute($model::PREFIX_UPDATED_USER.'id', $this->getUserId());
            $model->setAttribute($model::PREFIX_UPDATED_USER.'name', $this->getUserDisplayName());
        }
    }

    /**
     * deleting.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function recordDeleteUser(AbstractEntity $model): void
    {
        if (method_exists($model, 'getDeletedAtColumn') && !is_null($model::PREFIX_DELETED_USER)) {
            $model->setAttribute($model::PREFIX_DELETED_USER.'id', $this->getUserId());
            $model->setAttribute($model::PREFIX_DELETED_USER.'name', $this->getUserDisplayName());
        }
    }
}
