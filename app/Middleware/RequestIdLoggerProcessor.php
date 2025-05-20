<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Coroutine\Coroutine;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

use function App\get_request_id;

class RequestIdLoggerProcessor implements ProcessorInterface
{
    public function __invoke(array|LogRecord $record): array|LogRecord
    {
        $record['extra']['request_id'] = get_request_id();
        $record['extra']['coroutine_id'] = Coroutine::id();

        return $record;
    }
}
