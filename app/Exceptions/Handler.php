<?php

namespace App\Exceptions;

use App\Common\ApiReturnCode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_TOKNE_AUTHENTICATION_ERROR,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_TOKNE_AUTHENTICATION_ERROR), 401);
        } else if ($exception instanceof AuthorizationException) {
            throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_UNAUTHORIZED,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_UNAUTHORIZED), 403);
        }
        return parent::render($request, $exception);
    }

    public function convertExceptionToArray(Throwable $e)
    {
        return config('app.debug') ? [
            'code' => $e->getCode(),
            'msg' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];
    }
}
