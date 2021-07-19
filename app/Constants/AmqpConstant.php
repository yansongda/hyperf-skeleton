<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Amqp\Message\Type;

class AmqpConstant
{
    public const EXCHANGE = 'yansongda';

    public const EXCHANGE_TYPE = Type::TOPIC;

    public const ROUTING_KEY_YANSONGDA = 'yansongda';

    public const QUEUE_NAME_YANSONGDA = 'yansongda';
}
