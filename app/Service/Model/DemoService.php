<?php

declare(strict_types=1);

namespace App\Service\Model;

use App\Repository\DemoRepository;
use Hyperf\Di\Annotation\Inject;

class DemoService extends AbstractService
{
    /**
     * @Inject
     */
    protected DemoRepository $repository;
}
