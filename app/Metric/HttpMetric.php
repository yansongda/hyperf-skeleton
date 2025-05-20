<?php

declare(strict_types=1);

namespace App\Metric;

use App\Contract\MetricInterface;
use App\Model\Metric\AbstractMetric;
use App\Model\Metric\Http;
use App\Util\Logger;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Metric\Contract\MetricFactoryInterface;

class HttpMetric implements MetricInterface
{
    #[Inject]
    protected MetricFactoryInterface $metricFactory;

    public function metric(AbstractMetric $metric): void
    {
        if (!$metric instanceof Http) {
            return;
        }

        if ($metric->getDuration() > 1) {
            Logger::warning('[HttpMetric] 处理时间大于1s，请检查', ['duration' => $metric->getDuration()]);
        }

        $this->prometheus($metric);
    }

    protected function prometheus(Http $metric): void
    {
        $path = parse_url($metric->getEndpoint(), PHP_URL_PATH) ?? '/';

        $counter = $this->metricFactory->makeCounter('third_http_requests_total', ['code', 'class', 'path']);
        $counter->with(strval($metric->getCode()), $metric->getClass(), $path)
            ->add(1);

        $gauge = $this->metricFactory->makeGauge('third_http_request_duration_seconds', ['code', 'class', 'path']);
        $gauge->with(strval($metric->getCode()), $metric->getClass(), $path)
            ->set($metric->getDuration());

        $histogram = $this->metricFactory->makeHistogram('third_http_request_duration_seconds', ['code', 'class', 'path']);
        $histogram->with(strval($metric->getCode()), $metric->getClass(), $path)
            ->put($metric->getDuration());
    }
}
