<?php

use App\Exceptions\ApiHandlerException;
use App\Common\ApiReturnCode;

// 助手类


function api_error($code, int $statusCode = 500) :ApiHandlerException
{
    throw new ApiHandlerException($code, ApiReturnCode::getReturnMessage($code), $statusCode);
}