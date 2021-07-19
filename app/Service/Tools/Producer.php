<?php

declare(strict_types=1);

namespace App\Service\Tools;

use App\Util\Logger;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Di\Annotation\Inject;

class Producer
{
    /**
     * @Inject
     */
    protected \Hyperf\Amqp\Producer $producer;

    /**
     * producer.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function produce(ProducerMessageInterface $producerMessage, bool $confirm = false, int $timeout = 5): bool
    {
        $startTime = microtime(true);

        if ($this->producer->produce($producerMessage, $confirm, $timeout)) {
            Logger::info(
                '[Producer] 数据发往 MQ 成功',
                ['time' => microtime(true) - $startTime, 'data' => $producerMessage->payload()],
                ['mq']
            );

            return true;
        }

        Logger::error('[Producer] 发送数据到 MQ 失败，请检查 MQ 状态', func_get_args(), ['mq']);

        return false;
    }
}
