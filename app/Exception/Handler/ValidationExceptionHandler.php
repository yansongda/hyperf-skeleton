<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Constants\RequestConstant;
use App\Event\Metric;
use App\Model\Metric\Request;
use App\Util\Logger;
use Hyperf\Codec\Json;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\ValidationException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected EventDispatcherInterface $eventDispatcher;

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        /** @var ValidationException $throwable */
        $body = $throwable->validator->errors()->first();

        $data = [
            'code' => ErrorCode::PARAMS_INVALID,
            'message' => $body,
            'request_id' => $this->request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID),
        ];

        Logger::info('<-- 由于参数错误, 业务处理被中断', ['result' => $data, 'url' => $this->request->fullUrl(), 'inputs' => $this->request->all()]);

        $this->eventDispatcher->dispatch(new Metric(new Request([
            'code' => ErrorCode::PARAMS_INVALID->value,
            'url' => $this->request->fullUrl(),
        ])));

        return $response->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($data)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}
