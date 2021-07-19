<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * HealthController.
 *
 * @author yansongda <me@yansongda.cn>
 *
 * @Controller(prefix="health")
 */
class HealthController extends AbstractController
{
    /**
     * health.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @GetMapping(path="")
     */
    public function health(): string
    {
        return 'success';
    }
}
