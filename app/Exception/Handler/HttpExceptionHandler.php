<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Constants\RequestConstant;
use Hyperf\Codec\Json;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    #[Inject]
    protected RequestInterface $request;

    /**
     * @param HttpException $throwable
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        $data = [
            'code' => $throwable->getStatusCode(),
            'sub_code' => $throwable->getCode(),
            'message' => $throwable->getMessage(),
            'request_id' => $this->request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID),
        ];

        return $response
            ->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($data)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}
