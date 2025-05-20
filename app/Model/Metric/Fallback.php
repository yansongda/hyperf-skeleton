<?php

declare(strict_types=1);

namespace App\Model\Metric;

use App\Metric\FallbackMetric;

class Fallback extends AbstractMetric
{
    protected string $metric = FallbackMetric::class;

    private string $type = 'http';

    private string $endpoint = '';

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Fallback
    {
        $this->type = $type;

        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): Fallback
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
