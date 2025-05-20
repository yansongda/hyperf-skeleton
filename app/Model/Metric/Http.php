<?php

declare(strict_types=1);

namespace App\Model\Metric;

use App\Metric\HttpMetric;

class Http extends AbstractMetric
{
    protected string $metric = HttpMetric::class;

    private int $code = 0;

    private string $class = '';

    private string $endpoint = '';

    private float $duration = 0;

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): Http
    {
        $this->code = $code;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): Http
    {
        $this->class = $class;

        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): Http
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): Http
    {
        $this->duration = $duration;

        return $this;
    }
}
