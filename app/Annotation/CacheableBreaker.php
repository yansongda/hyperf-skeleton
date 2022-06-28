<?php

declare(strict_types=1);

namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Retry\CircuitBreakerState;

#[Attribute(Attribute::TARGET_METHOD)]
class CacheableBreaker extends AbstractAnnotation
{
    public function __construct(
        public ?string $fallback = '',
        public array $ignoreThrowables = [],
        public CircuitBreakerState|array $circuitBreakerState = ['resetTimeout' => 600]
    ) {
    }

    public function toArray(): array
    {
        if (is_array($this->circuitBreakerState)) {
            $this->circuitBreakerState = make(CircuitBreakerState::class, $this->circuitBreakerState);
        }

        return parent::toArray();
    }
}
