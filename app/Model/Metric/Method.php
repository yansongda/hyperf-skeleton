<?php

declare(strict_types=1);

namespace App\Model\Metric;

use App\Metric\MethodMetric;

class Method extends AbstractMetric
{
    protected string $metric = MethodMetric::class;

    private string $class = '';

    private string $method = '';

    private float $duration = 0;

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): Method
    {
        $this->class = $class;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): Method
    {
        $this->method = $method;

        return $this;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): Method
    {
        $this->duration = $duration;

        return $this;
    }
}
