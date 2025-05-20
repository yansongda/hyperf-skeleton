<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Constants\RequestConstant;
use App\Event\Metric;
use App\Model\Metric\Request;
use App\Util\Logger;
use Hyperf\Codec\Json;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class UnknownExceptionHandler extends ExceptionHandler
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected EventDispatcherInterface $eventDispatcher;

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        Logger::error('[UnknownExceptionHandler] 服务器内部错误: '.$throwable->getMessage(), [$throwable->getLine(), $throwable->getFile(), $throwable->getTraceAsString()]);

        $data = [
            'code' => ErrorCode::UNKNOWN_ERROR,
            'sub_code' => $throwable->getCode(),
            'message' => ErrorCode::getMessage(ErrorCode::UNKNOWN_ERROR),
            'request_id' => $this->request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID),
        ];

        // 防止报错 sys.ERROR 事务未提交
        Db::rollBack();

        $this->eventDispatcher->dispatch(new Metric(new Request([
            'code' => ErrorCode::UNKNOWN_ERROR->value,
            'url' => $this->request->fullUrl(),
        ])));

        return $response->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($data)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
