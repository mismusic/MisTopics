<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SocialitesController extends Controller
{
    // 第三方服务器授权验证
    public function authorization($type)
    {
        return Socialite::with($type)->redirect();  // 向认证服务器获取code值
    }

    public function callback(Request $request, $type)
    {
        $response = Http::asForm()->post(route(get_api_prefix() . 'authorizations.socialite', $type), [
            'code' => $request->code,
        ])->json();
        return response()->json($response);
    }
}
