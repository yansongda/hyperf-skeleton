<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Utils\Coroutine;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RequestIdLoggerProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): array|LogRecord
    {
        $record['extra']['request_id'] = get_request_id();
        $record['extra']['coroutine_id'] = Coroutine::id();

        return $record;
    }
}
