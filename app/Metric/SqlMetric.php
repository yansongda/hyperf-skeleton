<?php

declare(strict_types=1);

namespace App\Metric;

use App\Contract\MetricInterface;
use App\Model\Metric\AbstractMetric;
use App\Model\Metric\Sql;
use App\Util\Logger;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Metric\Contract\MetricFactoryInterface;

class SqlMetric implements MetricInterface
{
    #[Inject]
    protected MetricFactoryInterface $metricFactory;

    public function metric(AbstractMetric $metric): void
    {
        if (!$metric instanceof Sql) {
            return;
        }

        Logger::info(sprintf('[SqlMetric][%s][%s] %s', $metric->getConnection(), $metric->getDuration(), $metric->getSql()), [], ['channel' => 'sql']);

        if ($metric->getDuration() > 1) {
            Logger::warning('[SqlMetric] 数据库 sql 处理时间大于1s，请检查', ['duration' => $metric->getDuration(), 'sql' => $metric->getSql(), 'connection' => $metric->getConnection() ?? 'default']);
        }

        $this->prometheus($metric);
    }

    protected function prometheus(Sql $metric): void
    {
        $counter = $this->metricFactory->makeCounter('sql_requests_total', ['connection']);
        $counter->with($metric->getConnection())->add(1);

        $gauge = $this->metricFactory->makeGauge('sql_request_duration_seconds', ['connection']);
        $gauge->with($metric->getConnection())
            ->set($metric->getDuration());

        $histogram = $this->metricFactory->makeHistogram('sql_request_duration_seconds', ['connection']);
        $histogram->with($metric->getConnection())
            ->put($metric->getDuration());
    }
}
