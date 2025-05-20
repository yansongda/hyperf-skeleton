<?php

declare(strict_types=1);

namespace App\Model\Metric;

use App\Model\AbstractModel;

use function App\get_request_id;

abstract class AbstractMetric extends AbstractModel
{
    protected string $metric = '';

    protected int $msTimestamp;

    protected int $vccId = 0;

    protected string $requestId = '';

    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->setRequestId(get_request_id());
        $this->setMsTimestamp((int) (microtime(true) * 1000));
    }

    public function getMetric(): string
    {
        return $this->metric;
    }

    public function setMetric(string $metric): AbstractMetric
    {
        $this->metric = $metric;

        return $this;
    }

    public function getMsTimestamp(): int
    {
        return $this->msTimestamp;
    }

    public function setMsTimestamp(int $msTimestamp): AbstractMetric
    {
        $this->msTimestamp = $msTimestamp;

        return $this;
    }

    public function getVccId(): int
    {
        return $this->vccId;
    }

    public function setVccId(int $vccId): AbstractMetric
    {
        $this->vccId = $vccId;

        return $this;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): AbstractMetric
    {
        $this->requestId = $requestId;

        return $this;
    }
}
