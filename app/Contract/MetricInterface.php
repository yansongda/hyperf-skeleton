<?php

declare(strict_types=1);

namespace App\Contract;

use App\Model\Metric\AbstractMetric;

interface MetricInterface
{
    public function metric(AbstractMetric $metric): void;
}
