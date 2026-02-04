<?php

declare(strict_types=1);

namespace App\Api;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

use function App\get_request_id;

abstract class AbstractApiController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    public function success(?array $data = null, array $merge = []): array
    {
        $result = array_merge([
            'code' => 0,
            'message' => 'success',
            'request_id' => get_request_id(),
        ], $merge);

        if (!is_null($data)) {
            $result['data'] = $data;
        }

        return $result;
    }
}
