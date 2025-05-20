<?php

declare(strict_types=1);

namespace App\Event;

use App\Model\Metric\AbstractMetric;

class Metric
{
    public function __construct(public AbstractMetric $metric) {}
}
