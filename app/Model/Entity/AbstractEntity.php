<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Hyperf\Codec\Json;
use Hyperf\Database\Model\JsonEncodingException;
use Hyperf\DbConnection\Model\Model;
use Throwable;

abstract class AbstractEntity extends Model
{
    /**
     * 创建人前缀.
     */
    public const ?string PREFIX_CREATED_USER = 'created_user_';

    /**
     * 更新人前缀.
     */
    public const ?string PREFIX_UPDATED_USER = 'updated_user_';

    /**
     * 删除人前缀.
     */
    public const ?string PREFIX_DELETED_USER = 'deleted_user_';

    public static function new(): AbstractEntity
    {
        return new static(); // @phpstan-ignore-line
    }

    public function toJson($options = 0): false|string
    {
        try {
            $json = Json::encode($this->jsonSerialize());
        } catch (Throwable $e) {
            throw JsonEncodingException::forModel($this, $e->getMessage());
        }

        return $json;
    }

    protected function asJson(mixed $value): false|string
    {
        return Json::encode($value);
    }
}
