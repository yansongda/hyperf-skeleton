<?php

declare(strict_types=1);

namespace App\Aspect;

use App\Annotation\CacheBreaker;
use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Util\Logger;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Must handled before `Hyperf\Cache\Aspect\CacheableAspect`.
 *
 * @Aspect
 */
class CacheableAspect extends AbstractAspect
{
    /**
     * @var array
     */
    public $annotations = [
        Cacheable::class,
    ];

    /**
     * @var int
     */
    public $priority = 100;

    protected ContainerInterface $container;

    protected array $downgradeInfo = [];

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
     *
     * @return mixed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $downgradeInfo = $this->getDowngradeInfo($proceedingJoinPoint);

        if ($downgradeInfo['next_cacheable'] ?? 0 > time()) {
            return $this->doDowngrade($proceedingJoinPoint, $downgradeInfo['fallback'] ?? [], $downgradeInfo['exception'] ?? new ApiException(ErrorCode::INTERNAL_PARAMS_ERROR));
        }

        try {
            return $proceedingJoinPoint->process();
        } catch (Throwable $exception) {
            $annotation = $this->getBreakerAnnotation($proceedingJoinPoint);

            if (!$annotation || is_null($annotation->fallback) || $this->inIgnoreThrowable($annotation->ignoreThrowables, $exception)) {
                throw $exception;
            }

            return $this->downgrade($proceedingJoinPoint, $annotation, $exception);
        }
    }

    /**
     * 降级.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    protected function downgrade(ProceedingJoinPoint $proceedingJoinPoint, CacheBreaker $annotation, Throwable $exception)
    {
        $proceedingJoinPoint->arguments[] = $exception;

        $fallback = $this->getFallback($annotation);

        $this->recordDowngradeInfo($this->getRecordingKey($proceedingJoinPoint), $annotation, $exception);

        Logger::error(
            '[Cache] Redis 缓存系统访问异常，已进行熔断降级处理，将在设置的 ttl 内直接从源数据源获取，请立即检查',
            ['message' => $exception->getMessage(), 'class' => $proceedingJoinPoint->className, 'method' => $proceedingJoinPoint->methodName, 'exception' => $exception->getTraceAsString()]
        );

        return $this->doDowngrade($proceedingJoinPoint, $fallback, $exception);
    }

    /**
     * 进行降级.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    protected function doDowngrade(ProceedingJoinPoint $proceedingJoinPoint, array $fallback, Throwable $exception)
    {
        if (empty($fallback)) {
            return $proceedingJoinPoint->processOriginalMethod();
        }

        $arguments = $proceedingJoinPoint->getArguments();

        if (is_callable($fallback)) {
            return call_user_func($fallback, ...$arguments);
        }

        throw $exception;
    }

    protected function inIgnoreThrowable(array $arr, Throwable $t): bool
    {
        return (bool) Arr::first(
            $arr,
            function ($v) use ($t) {
                return $t instanceof $v;
            }
        );
    }

    /**
     * 获取降级回调.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getFallback(CacheBreaker $annotation): array
    {
        $fallback = [];

        if (strpos($annotation->fallback ?? '', '@') > 0) {
            [$class, $method] = explode('@', $annotation->fallback);
            $fallback = [$this->container->get($class), $method];
        }

        return $fallback;
    }

    /**
     * 记录降级信息.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function recordDowngradeInfo(string $key, CacheBreaker $annotation, Throwable $exception): void
    {
        $fallback = $this->getFallback($annotation);

        if (!empty($annotation->resetTimeout)) {
            $this->downgradeInfo[$key] = [
                'reset_timeout' => $annotation->resetTimeout,
                'next_cacheable' => $annotation->resetTimeout + time(),
                'fallback' => $fallback,
                'exception' => $exception,
            ];
        }
    }

    protected function getDowngradeInfo(ProceedingJoinPoint $proceedingJoinPoint): array
    {
        $recordingKey = $this->getRecordingKey($proceedingJoinPoint);

        return $this->downgradeInfo[$recordingKey] ?? [];
    }

    /**
     * 获取记录的 key.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getRecordingKey(ProceedingJoinPoint $proceedingJoinPoint): string
    {
        return $proceedingJoinPoint->className.'_'.$proceedingJoinPoint->methodName;
    }

    protected function getBreakerAnnotation(ProceedingJoinPoint $proceedingJoinPoint): ?CacheBreaker
    {
        $meta = $proceedingJoinPoint->getAnnotationMetadata();

        return $meta->method[CacheBreaker::class] ?? null;
    }
}
