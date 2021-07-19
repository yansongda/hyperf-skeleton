<?php

declare(strict_types=1);

namespace App\Exception;

use App\Constants\ErrorCode;
use Throwable;

class InternalException extends ApiException
{
    /**
     * Bootstrap.
     */
    public function __construct(int $code = ErrorCode::INTERNAL_PARAMS_ERROR, ?string $message = null, ?array $raw = null, Throwable $previous = null)
    {
        parent::__construct($code, $message, $raw, $previous);
    }
}
