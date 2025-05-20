<?php

declare(strict_types=1);

namespace App\Exception;

use App\Constants\ErrorCode;
use Exception;
use Throwable;

class ApiException extends Exception
{
    public ?array $raw = null;

    public function __construct(ErrorCode $code = ErrorCode::UNKNOWN_ERROR, ?string $message = null, ?array $raw = null, ?Throwable $previous = null)
    {
        if (empty($message)) {
            $message = ErrorCode::getMessage($code);
        }

        if (!is_null($raw)) {
            $this->raw = $raw;
        }

        parent::__construct($message, $code->value, $previous);
    }
}
