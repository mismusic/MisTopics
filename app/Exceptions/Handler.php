<?php

namespace App\Exceptions;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseJson;

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
        if ($exception instanceof MethodNotAllowedHttpException) {
            // 请求方法不允许的时候，抛出错误
            return $this->returnJson(ApiReturnCode::API_RETURN_CODE_METHOD_NOT_ALLOWED, ['msg' => $exception->getMessage()], 405);
            //die;
        } else if ($exception instanceof AuthenticationException) {
            if ($request->expectsJson()) {
                // 未授权的时候返回
                api_error(ApiReturnCode::API_RETURN_CODE_UNAUTHORIZED, 401);
            }
        } else if ($exception instanceof AuthorizationException) {
            if ($request->expectsJson()) {
                // 拒绝执行的时候返回
                api_error(ApiReturnCode::API_RETURN_CODE_FORBIDDEN, 403);
            }
        } else if ($exception instanceof ValidationException) {
            if ($request->expectsJson()) {  // 当请求数据的类型为Json时，才执行该逻辑
                // 请求参数不符合验证规则的时候返回
                return $this->returnJson(ApiReturnCode::API_RETURN_CODE_VALIDATOR_FAILED, $exception->errors(), 422);
            }
        } else if ($exception instanceof ModelNotFoundException) {
            if ($request->expectsJson()) {
                return api_error(ApiReturnCode::API_RETURN_CODE_NOT_FOUND, 404);
            }
        } else if ($exception instanceof ApiHandlerException) {
            /*if (! $request->expectsJson()) {
                return view('shared.api_exception', ['msg' => $exception->getMessage()]);
            }*/
        }
        return parent::render($request, $exception);
    }

    public function convertExceptionToArray(Throwable $e)
    {
        return config('app.debug') ? [
            'msg' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'code' => $this->isHttpException($e) ? $e->getCode() : ApiReturnCode::API_RETURN_CODE_SERVER_ERROR,
            'msg' => $this->isHttpException($e) ? $e->getMessage() : ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SERVER_ERROR),
        ];
    }
}
