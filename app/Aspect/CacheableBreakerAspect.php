<?php

declare(strict_types=1);

namespace App\Aspect;

use App\Annotation\CacheableBreaker;
use App\Util\Logger;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Throwable;

#[Aspect]
class CacheableBreakerAspect extends AbstractAspect
{
    public array $annotations = [
        CacheableBreaker::class,
    ];

    public ?int $priority = 100;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * process.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        $annotation = $this->getAnnotation($proceedingJoinPoint);
        $fallback = $this->getFallback($annotation);

        if ($annotation->circuitBreakerState->isOpen()) {
            return $this->downgrade($proceedingJoinPoint, $fallback);
        }

        try {
            return $proceedingJoinPoint->process();
        } catch (Throwable $throwable) {
            if ($this->inIgnoreThrowable($annotation->ignoreThrowables, $throwable)) {
                throw $throwable;
            }

            Logger::error('[CacheableBreakerAspect] 获取缓存失败，设置的时间内（默认10分钟）熔断降级从数据库中获取，请立即检查缓存服务状态', ['message' => $throwable->getMessage(), 'trace' => $throwable->getTrace()]);

            $annotation->circuitBreakerState->open();
        }

        return $this->downgrade($proceedingJoinPoint, $fallback);
    }

    /**
     * 进行降级.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Throwable
     */
    protected function downgrade(ProceedingJoinPoint $proceedingJoinPoint, ?array $fallback): mixed
    {
        if (empty($fallback)) {
            return $proceedingJoinPoint->processOriginalMethod();
        }

        $arguments = $proceedingJoinPoint->getArguments();

        return call_user_func($fallback, ...$arguments);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getFallback(?CacheableBreaker $annotation): ?array
    {
        if (is_null($annotation)) {
            return null;
        }

        if (strpos($annotation->fallback ?? '', '@') > 0) {
            [$class, $method] = explode('@', $annotation->fallback);
            $fallback = [$this->container->get($class), $method];
        }

        return $fallback ?? null;
    }

    protected function getAnnotation(ProceedingJoinPoint $proceedingJoinPoint): ?CacheableBreaker
    {
        $meta = $proceedingJoinPoint->getAnnotationMetadata();

        $annotation = $meta->method[CacheableBreaker::class] ?? null;

        if (!is_null($annotation)) {
            $annotation->toArray();
        }

        return $annotation;
    }

    protected function inIgnoreThrowable(array $arr, Throwable $t): bool
    {
        return !is_null(Arr::first($arr, function ($v) use ($t) {
            return $t instanceof $v;
        }));
    }
}
