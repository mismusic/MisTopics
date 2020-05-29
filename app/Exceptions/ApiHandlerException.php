<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiHandlerException extends HttpException
{

    public function __construct(int $code = 0, string $message = null,  ?int $statusCode = 200, \Throwable $previous = null)
    {
        parent::__construct($statusCode, $message, $previous, [], $code);
    }

}
