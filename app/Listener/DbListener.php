<?php

declare(strict_types=1);

namespace App\Listener;

use App\Event\Metric;
use App\Model\Metric\Sql;
use App\Util\Logger;
use Hyperf\Collection\Arr;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

#[Listener]
class DbListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container) {}

    public function listen(): array
    {
        return [
            TransactionBeginning::class,
            QueryExecuted::class,
            TransactionCommitted::class,
            TransactionRolledBack::class,
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(object $event): void
    {
        if ($event instanceof TransactionBeginning) {
            Logger::info('[DbListener] 数据库事务开始', [], ['channel' => 'sql']);
        }

        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            if (!Arr::isAssoc($event->bindings)) {
                foreach ($event->bindings as $value) {
                    $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                }
            }

            $this->metric($sql, $event->time, $event->connectionName);
        }

        if ($event instanceof TransactionCommitted) {
            Logger::info('[DbListener] 数据库事务已提交', [], ['channel' => 'sql']);
        }

        if ($event instanceof TransactionRolledBack) {
            Logger::info('[DbListener] 数据库事务已回滚', [], ['channel' => 'sql']);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function metric(string $sql, float $duration, string $connection): void
    {
        $this->container->get(EventDispatcherInterface::class)->dispatch(new Metric(new Sql([
            'connection' => $connection,
            'sql' => $sql,
            'duration' => $duration / 1000,
        ])));
    }
}
