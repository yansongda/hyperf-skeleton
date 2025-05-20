<?php

declare(strict_types=1);

namespace App\Metric;

use App\Contract\MetricInterface;
use App\Model\Metric\AbstractMetric;

class FallbackMetric implements MetricInterface
{
    public function metric(AbstractMetric $metric): void {}
}
