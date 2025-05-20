<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

abstract class AbstractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 原始验证.
     */
    public function originalValidated(): array
    {
        return parent::validated();
    }

    /**
     * 原始的 null 消息不能过滤.
     */
    public function validated(): array
    {
        return array_filter($this->originalValidated(), function ($value) {
            return !is_string($value) || '' !== trim($value);
        }, ARRAY_FILTER_USE_BOTH);
    }
}
