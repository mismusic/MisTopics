<?php

namespace App\Http\Middleware;

use Closure;

class AcceptRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        // 根据http请求头部 Accept-language 来进行语言的返回
        $lang = $request->headers->get('Accept-language');
        if ($lang) {
            app()->setLocale($lang);
        }
        return $next($request);
    }
}
