<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Entity\Demo;

class DemoRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Demo::class);
    }
}
