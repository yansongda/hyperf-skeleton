<?php

declare(strict_types=1);

namespace App\Aspect;

use App\Annotation\RecordElasticsearchLogger;
use App\Constants\ErrorCode;
use App\Exception\InternalException;
use App\Util\Logger;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Throwable;

/**
 * @Aspect()
 */
class RecordElasticsearchLoggerAspect extends AbstractAspect
{
    /**
     * annotations.
     *
     * @var array
     */
    public $annotations = [
        RecordElasticsearchLogger::class,
    ];

    /**
     * process.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        Logger::info('----> 准备请求 elasticsearch 服务', ['method' => $proceedingJoinPoint->methodName, 'args' => $proceedingJoinPoint->getArguments()]);

        $startTime = microtime(true);

        try {
            $result = $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            Logger::error('<---- 处理 elasticsearch 服务出错', ['message' => $e->getMessage(), 'trace' => $e->getTrace()]);

            throw new InternalException(ErrorCode::ELASTICSEARCH_ERROR, $e->getMessage());
        }

        Logger::info('<---- 处理 elasticsearch 服务完毕', ['time' => microtime(true) - $startTime, 'result' => $result]);

        return $result;
    }
}
