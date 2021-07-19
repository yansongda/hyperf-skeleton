<?php

declare(strict_types=1);

use App\Constants\RequestConstant;
use App\Util\Context;
use Psr\Http\Message\ServerRequestInterface;

if (!function_exists('get_request_id')) {
    /**
     * 获取当前协程内的 request_id.
     */
    function get_request_id(): string
    {
        $request = Context::get(ServerRequestInterface::class);

        if (!is_null($request)) {
            return $request->getHeaderLine(RequestConstant::HEADER_REQUEST_ID);
        }

        return Context::get(RequestConstant::HEADER_REQUEST_ID, '');
    }
}
