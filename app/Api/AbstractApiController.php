<?php

declare(strict_types=1);

namespace App\Api;

use App\Constants\RequestConstant;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Yansongda\Supports\Arr;

abstract class AbstractApiController
{
    /**
     * @Inject
     */
    protected ContainerInterface $container;

    /**
     * @Inject
     */
    protected RequestInterface $request;

    /**
     * @Inject
     */
    protected ResponseInterface $response;

    public function success(?array $data = null, array $merge = []): array
    {
        $result = array_merge([
            'code' => 0,
            'message' => '操作成功',
            'request_id' => $this->request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID),
        ], $merge);

        if (!is_null($data)) {
            $result['data'] = Arr::camelCaseKey($data);
        }

        return $result;
    }

    public function asterisk(?array $data = null): array
    {
        return array_merge([
            'code' => 200,
            'msg' => 'success',
            'request_id' => $this->request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID),
        ], (array) $data);
    }
}
