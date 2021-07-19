<?php

declare(strict_types=1);

namespace App\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class CacheBreaker extends AbstractAnnotation
{
    public int $resetTimeout = 600;

    public array $ignoreThrowables = [];

    public ?string $fallback = '';

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function __construct(?array $value = null)
    {
        parent::__construct($value);
    }
}
