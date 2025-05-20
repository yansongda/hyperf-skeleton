<?php

declare(strict_types=1);

namespace App\Metric;

use App\Contract\MetricInterface;
use App\Model\Metric\AbstractMetric;
use App\Model\Metric\Method;
use App\Util\Logger;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Metric\Contract\MetricFactoryInterface;

class MethodMetric implements MetricInterface
{
    #[Inject]
    protected MetricFactoryInterface $metricFactory;

    public function metric(AbstractMetric $metric): void
    {
        if (!$metric instanceof Method) {
            return;
        }

        if ($metric->getDuration() > 0.003) {
            Logger::warning('[MethodMetric] 处理时间大于0.003s，请检查', ['duration' => $metric->getDuration(), 'class' => $metric->getClass(), 'method' => $metric->getMethod()]);
        }

        $this->prometheus($metric);
    }

    protected function prometheus(Method $metric): void
    {
        $counter = $this->metricFactory->makeCounter('method_invoke_total', ['class', 'method']);
        $counter->with($metric->getClass(), $metric->getMethod())->add(1);

        $gauge = $this->metricFactory->makeGauge('method_invoke_duration_seconds', ['class', 'method']);
        $gauge->with($metric->getClass(), $metric->getMethod())
            ->set($metric->getDuration());

        $histogram = $this->metricFactory->makeHistogram('method_invoke_duration_seconds', ['class', 'method']);
        $histogram->with($metric->getClass(), $metric->getMethod())
            ->put($metric->getDuration());
    }
}
