<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;
use Yansongda\Supports\Arr;

abstract class AbstractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 原始验证.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function originalValidated(): array
    {
        return parent::validated();
    }

    /**
     * 原始的 null 消息不能过滤.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function validated(): array
    {
        return Arr::snakeCaseKey(array_filter($this->originalValidated(), function ($value) {
            return !is_string($value) || '' !== trim($value);
        }, ARRAY_FILTER_USE_BOTH));
    }
}
