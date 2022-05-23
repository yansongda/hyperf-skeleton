<?php

declare(strict_types=1);

namespace App\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Retry\CircuitBreakerState;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class CacheableBreaker extends AbstractAnnotation
{
    /**
     * @var CircuitBreakerState|array
     */
    public $circuitBreakerState = [
        'resetTimeout' => 600,
    ];

    /**
     * @var array|string[]
     */
    public array $ignoreThrowables = [];

    public ?string $fallback = '';

    public function toArray(): array
    {
        if (is_array($this->circuitBreakerState)) {
            $this->circuitBreakerState = make(CircuitBreakerState::class, $this->circuitBreakerState);
        }

        return parent::toArray();
    }
}
