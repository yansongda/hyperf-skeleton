<?php

declare(strict_types=1);

namespace App\Listener;

use App\Constants\RequestConstant;
use App\Util\Context;
use App\Util\Producer;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class WaitingProduceListener implements ListenerInterface
{
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

        if (empty($producer)) {
            return;
        }

        foreach ($producer as $p) {
            Producer::produce($p);
        }

        Context::set(RequestConstant::AFTER_DB_TO_PRODUCE, null);
    }
}
