<?php

declare(strict_types=1);

namespace App\Aspect;

use App\Annotation\RecordRequestLogger;
use App\Exception\ApiException;
use App\Util\Logger;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Throwable;

#[Aspect]
class RecordRequestLoggerAspect extends AbstractAspect
{
    public array $annotations = [
        RecordRequestLogger::class,
    ];

    public ?int $priority = 1000;

    #[Inject]
    protected RequestInterface $request;

    /**
     * process.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        if (str_contains($this->request->getHeaderLine('user-agent'), 'kube-probe')) {
            return $proceedingJoinPoint->process();
        }

        Logger::info(
            '--> 处理业务请求',
            [
                'url' => $this->request->fullUrl(),
                'inputs' => $this->request->all(),
                'header' => $this->request->getHeaders(),
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
