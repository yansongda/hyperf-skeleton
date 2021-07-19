<?php

declare(strict_types=1);

namespace App\Aspect;

use App\Annotation\RecordRequestLogger;
use App\Constants\RequestConstant;
use App\Exception\ApiException;
use App\Util\Logger;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Throwable;

/**
 * @Aspect(priority=2)
 */
class RecordRequestLoggerAspect extends AbstractAspect
{
    /**
     * annotations.
     *
     * @var array
     */
    public $annotations = [
        RecordRequestLogger::class,
    ];

    /**
     * @Inject
     */
    protected RequestInterface $request;

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
        if (false !== strpos($this->request->getHeaderLine('user-agent'), 'kube-probe')) {
            return $proceedingJoinPoint->process();
        }

        Logger::info(
            '--> 处理业务请求',
            [
                'url' => $this->request->fullUrl(),
                'inputs' => $this->request->all(),
                'auth' => $this->request->getAttribute(RequestConstant::ATTRIBUTE_AUTH),
            ]
        );

        $startTime = microtime(true);

        try {
            $result = $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            if ($e instanceof ApiException) {
                Logger::info('<-- 业务处理被中断', ['time' => microtime(true) - $startTime, 'code' => $e->getCode(), 'message' => $e->getMessage(), 'raw' => $e->raw]);
            }

            throw $e;
        }

        Logger::info('<-- 处理业务请求完毕', ['time' => microtime(true) - $startTime, 'result' => $result]);

        return $result;
    }
}
