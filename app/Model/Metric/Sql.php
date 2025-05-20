<?php

declare(strict_types=1);

namespace App\Model\Metric;

use App\Metric\SqlMetric;

class Sql extends AbstractMetric
{
    protected string $metric = SqlMetric::class;

    private string $connection = 'default';

    private string $sql = '';

    private float $duration = 0;

    public function getConnection(): string
    {
        return $this->connection;
    }

    public function setConnection(string $connection): Sql
    {
        $this->connection = $connection;

        return $this;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function setSql(string $sql): Sql
    {
        $this->sql = $sql;

        return $this;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): Sql
    {
        $this->duration = $duration;

        return $this;
    }
}
