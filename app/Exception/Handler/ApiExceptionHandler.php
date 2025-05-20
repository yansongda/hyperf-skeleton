<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Constants\RequestConstant;
use App\Exception\ApiException;
use Hyperf\Codec\Json;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ApiExceptionHandler extends ExceptionHandler
{
    #[Inject]
    protected RequestInterface $request;

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        $data = [
            'code' => $throwable->getCode(),
            'message' => $this->getMessage($throwable),
            'request_id' => $this->request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID),
        ];

        if (property_exists($throwable, 'raw') && !is_null($throwable->raw)) {
            $data['data'] = $throwable->raw;
        }

        return $response
            ->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($data)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ApiException;
    }

    protected function getMessage(Throwable $throwable): string
    {
        return '' === $throwable->getMessage() ? ErrorCode::getMessage($throwable->getCode()) : $throwable->getMessage();
    }
}
