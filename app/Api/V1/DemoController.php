<?php

declare(strict_types=1);

namespace App\Api\V1;

use App\Annotation\RecordRequestLogger;
use App\Api\AbstractApiController;
use Hyperf\HttpServer\Annotation\Controller;

/**
 * @Controller(prefix="api/v1/demo")
 * @RecordRequestLogger()
 */
class DemoController extends AbstractApiController
{
}
