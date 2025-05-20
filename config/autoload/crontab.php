<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'enable' => env('ENABLE_CRONTAB', false),
];
