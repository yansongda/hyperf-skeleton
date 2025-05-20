<?php

declare(strict_types=1);

namespace App\Api\V1;

use App\Api\AbstractApiController;
use App\Util\Logger;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller(prefix: 'api/v1/demo')]
class DemoController extends AbstractApiController
{
    #[RequestMapping(path: '')]
    public function health(): array
    {
        Logger::info('demo');

        return $this->success();
    }
}
