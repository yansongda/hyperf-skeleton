<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Constants\RequestConstant;
use App\Util\Logger;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class UnknownExceptionHandler extends ExceptionHandler
{
    /**
     * @Inject
     */
    protected ServerRequestInterface $request;

    /**
     * handle.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        Logger::error(
            '[UnknownExceptionHandler] 服务器内部错误: '.$throwable->getMessage(),
            [$throwable->getLine(), $throwable->getFile(), $throwable->getTraceAsString()]);

        $data = json_encode([
            'code' => ErrorCode::UNKNOWN_ERROR,
            'sub_code' => $throwable->getCode(),
            'message' => ErrorCode::getMessage(ErrorCode::UNKNOWN_ERROR),
            'request_id' => $this->request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID),
        ]);

        // 防止报错 sys.ERROR 事务未提交
        Db::rollBack();

        return $response
            ->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withBody(new SwooleStream($data));
    }

    /**
     * isValid.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
