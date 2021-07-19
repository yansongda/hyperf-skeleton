<?php

declare(strict_types=1);

namespace App\Listener;

use App\Constants\RequestConstant;
use App\Service\Tools\Producer;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Utils\Context;

/**
 * @Listener
 */
class WaitingProduceListener implements ListenerInterface
{
    /**
     * @Inject
     */
    protected Producer $producer;

    /**
     * listen.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function listen(): array
    {
        return [
            TransactionCommitted::class,
        ];
    }

    public function process(object $event): void
    {
        if (!($event instanceof TransactionCommitted)) {
            return;
        }

        $producer = Context::get(RequestConstant::AFTER_DB_TO_PRODUCE);

        if (is_null($producer) || empty($producer)) {
            return;
        }

        foreach ($producer as $p) {
            $this->producer->produce($p);
        }

        Context::set(RequestConstant::AFTER_DB_TO_PRODUCE, null);
    }
}
