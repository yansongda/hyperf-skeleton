<?php

declare(strict_types=1);

namespace App\Metric;

use App\Contract\MetricInterface;
use App\Model\Metric\AbstractMetric;
use App\Model\Metric\Request;
use App\Util\Logger;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Metric\Contract\MetricFactoryInterface;

class RequestMetric implements MetricInterface
{
    #[Inject]
    protected MetricFactoryInterface $metricFactory;

    public function metric(AbstractMetric $metric): void
    {
        if (!$metric instanceof Request) {
            return;
        }

        $extra = $metric->getExtra();

        if (0 === $metric->getCode()) {
            Logger::info('<-- 处理业务请求完毕', ['time' => $metric->getDuration(), 'response' => $extra['response'] ?? null]);
        } else {
            Logger::info('<-- 业务处理被中断', ['time' => $metric->getDuration(), 'code' => $metric->getCode(), 'message' => $extra['message'] ?? '', 'raw' => $extra['raw'] ?? null]);
        }

        if ($metric->getDuration() > 10) {
            Logger::warning('[RequestMetric] 处理时间大于10s，请检查', ['duration' => $metric->getDuration()]);
        }

        $this->prometheus($metric);
    }

    protected function prometheus(Request $metric): void
    {
        $path = parse_url($metric->getUrl(), PHP_URL_PATH) ?? '/';

        $counter = $this->metricFactory->makeCounter('http_requests_total', ['path', 'code']);
        $counter->with($path, strval($metric->getCode()))->add(1);

        $gauge = $this->metricFactory->makeGauge('http_request_duration_seconds', ['path', 'code']);
        $gauge->with($path, strval($metric->getCode()))
            ->set($metric->getDuration());

        $histogram = $this->metricFactory->makeHistogram('http_request_duration_seconds', ['path', 'code']);
        $histogram->with($path, strval($metric->getCode()))
            ->put($metric->getDuration());
    }
}
