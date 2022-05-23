<?php

declare(strict_types=1);

use App\Constants\RequestConstant;
use App\Util\Context;
use App\Util\Logger;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Psr\Http\Message\ServerRequestInterface;

if (!function_exists('get_request_id')) {
    /**
     * 获取当前协程内的 request_id.
     */
    function get_request_id(): string
    {
        $request = Context::get(ServerRequestInterface::class);

        if (!is_null($request)) {
            return $request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID);
        }

        return Context::get(RequestConstant::HEADER_REQUEST_ID, '');
    }
}

if (!function_exists('do_parallel')) {
    function do_parallel(array $calls, int $concurrent = 30, bool $throw = false): array
    {
        try {
            $results = parallel($calls, $concurrent);
        } catch (ParallelExecutionException $e) {
            if ($throw) {
                throw $e;
            }

            $throwable = $e->getThrowables();
            $results = $e->getResults();

            foreach ($throwable as $key => $throw) {
                /* @var \Throwable $throw */
                Logger::error('[Functions] 并发执行失败，已忽略失败部分', ['key' => $key, 'message' => $throw->getMessage(), 'trace' => $throw->getTrace()]);
            }
        }

        return $results ?? [];
    }
}
