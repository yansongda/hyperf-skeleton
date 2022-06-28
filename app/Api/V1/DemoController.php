<?php

declare(strict_types=1);

namespace App\Api\V1;

use App\Annotation\RecordRequestLogger;
use App\Api\AbstractApiController;
use App\Util\Logger;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: 'api/v1/demo')]
#[RecordRequestLogger]
class DemoController extends AbstractApiController
{
    #[GetMapping(path: '')]
    public function health(): array
    {
        Logger::info('demo');

        return $this->success();
    }
}
