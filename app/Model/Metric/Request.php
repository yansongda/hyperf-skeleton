<?php

declare(strict_types=1);

namespace App\Model\Metric;

use App\Constants\ErrorCode;
use App\Metric\RequestMetric;

class Request extends AbstractMetric
{
    protected string $metric = RequestMetric::class;

    private int $code = 0;

    private string $url = '';

    private float $duration = 0;

    private array $extra = [];

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(ErrorCode|int $code): Request
    {
        if ($code instanceof ErrorCode) {
            $code = $code->value;
        }

        $this->code = $code;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Request
    {
        $this->url = $url;

        return $this;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): Request
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }

    public function setExtra(array $extra): Request
    {
        $this->extra = $extra;

        return $this;
    }
}
