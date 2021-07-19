<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Hyperf\DbConnection\Model\Model;
use Yansongda\Supports\Arr;

abstract class AbstractEntity extends Model
{
    /**
     * 创建人前缀.
     *
     * @var string|null
     */
    public const PREFIX_CREATED_USER = 'created_user_';

    /**
     * 更新人前缀.
     *
     * @var string|null
     */
    public const PREFIX_UPDATED_USER = 'updated_user_';

    /**
     * 删除人前缀.
     *
     * @var string|null
     */
    public const PREFIX_DELETED_USER = 'deleted_user_';

    /**
     * new.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return static
     */
    public static function new(): AbstractEntity
    {
        return new static();
    }

    /**
     * toCamelKeyArray.
     *
     * 前端需要驼峰形式
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function toCamelKeyArray(): array
    {
        return Arr::camelCaseKey($this->toArray());
    }
}
