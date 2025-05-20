<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: '/')]
class RootController extends AbstractController
{
    #[GetMapping(path: 'favicon.ico')]
    public function favicon(): string
    {
        return '';
    }

    #[GetMapping(path: 'health')]
    public function health(): string
    {
        return 'success';
    }

    #[GetMapping(path: 'ping')]
    public function ping(): string
    {
        return 'pong';
    }
}
