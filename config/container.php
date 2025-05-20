<?php

declare(strict_types=1);

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Context\ApplicationContext;

$container = new Container((new DefinitionSourceFactory())());

return ApplicationContext::setContainer($container);
