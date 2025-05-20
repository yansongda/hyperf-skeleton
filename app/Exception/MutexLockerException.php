<?php

declare(strict_types=1);

namespace App\Exception;

use App\Constants\ErrorCode;
use Throwable;

class MutexLockerException extends InternalException
{
    public function __construct(ErrorCode $code = ErrorCode::INTERNAL_MUTEX_LOCKER_ERROR, ?string $message = null, ?array $raw = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $raw, $previous);
    }
}
