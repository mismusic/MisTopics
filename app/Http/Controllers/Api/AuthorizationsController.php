<?php

namespace App\Http\Controllers\Api;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use App\Common\Traits\VerificationPhone;
use App\Exceptions\ApiHandlerException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuthorizationRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;

class AuthorizationsController extends Controller
{

    use ResponseJson, VerificationPhone;

    const TOKEN_TTL = 120; // 设置token的生效时间

    public function socialite(AuthorizationRequest $request, $type)
    {
        // 1.获取授权的用户信息
        $socialite = Socialite::driver($type);
        try {
            $response = $socialite->getAccessTokenResponse($request->code);  // 通过code获取对应的access_token和openid
        } catch (\Exception $e) {
            $rtn = $this->returnJson(ApiReturnCode::API_RETURN_CODE_CODE_INVALID,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_CODE_INVALID));
            return response()->json($rtn);
        }
        $socialite->setOpenId($response['openid']);
        // 根据access_token和openid来获取授权用户信息
        $oAuthUser = $socialite->userFromToken($response['access_token']);
        $token = '';
        switch ($type) {
            case 'weixin':
                $openid = $oAuthUser->getId();
                $unionid = $oAuthUser->offsetExists('unionid') ? $oAuthUser->offsetGet('unionid') : null;
                if ($unionid) {
                    $user = User::query()->where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::query()->where('weixin_openid', $openid)->first();
                }
                // 如果用户不存在就进行创建
                if (! $user) {
                    $user = User::create([
                        'username' => $oAuthUser->getNickname(),
                        'email' => $oAuthUser->getEmail(),
                        'avatar' => $oAuthUser->getAvatar(),
                        'weixin_openid' => $openid,
                        'weixin_unicode' => $unionid,
                    ]);
                }
                // 2.对用户进行登录，返回对应的access_token值
                $token = auth()->setTTL(self::TOKEN_TTL)->useResponsable(false)->login($user)->get();  // 这里时间的单位为秒钟
                break;
        }
        // 如果 access_token不存在，就抛出一个错误
        if (empty($token)) {
            $rtn = $this->returnJson(ApiReturnCode::API_RETURN_CODE_GET_TOKEN_ERROR,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_GET_TOKEN_ERROR),
                ['socialite_type' => $type]);
            return response()->json($rtn);
        }
        // 3.返回 access_token 到客户端
        return response()->json($this->responseWithToken($token));
    }

    public function token(AuthorizationRequest $request)
    {
        // 1.判断请求参数 password 是否存在，如果存在就进行登录
        $phone = $request->phone;
        if ($request->input('password')) {
            // 使用手机 + 密码登录的方式
            $credentials = [
                'phone' => $phone,
                'password' => $request->input('password'),
            ];
            // 进行用户的登录认证，认证成功则返回一个token值
            $tokenObj = auth()->useResponsable(false)->setTTL(self::TOKEN_TTL)->attempt($credentials);
            if (! $tokenObj) {
                throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_PHONE_OR_PASSWORD_ERROR,
                    ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_PHONE_OR_PASSWORD_ERROR), 401);
            }
            $token = $tokenObj->get();
        } else {
            // 验证短信验证码 + 手机是否正确
            $this->smsVerify();

            // 2.判断phone是否已经存在用户表，如果不存在就进行注册
            $user = User::query()->where('phone', $phone)->first();  // 根据手机号码来获取用户模型数据
            if (! $user) {
                // 执行 手机号 + 验证码 进行注册的逻辑
                $user = User::create([
                    'phone' => $phone,
                    'username' => 'user_' . $phone,  // 默认生成一个用户名
                ]);
            }
            // 对用户进行登录认证，然后获取到token值
            $token = auth()->setTTL(self::TOKEN_TTL)->useResponsable(false)->login($user)->get();
        }

        // 3.返回access_token到客户端
        $rtn = $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS,
            ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SUCCESS),
            $this->responseWithToken($token));
        return response()->json($rtn);
    }

    /**
     * 注销JWT Token值
     */
    public function logout()
    {
        auth()->logout();  // 删除当前的token值
        throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_SUCCESS,
            ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SUCCESS), 200);
    }

    public function refresh()
    {
        // 获取一个新的token值
        $token = auth()->useResponsable(false)->setTTL(self::TOKEN_TTL)->refresh()->get();  // 刷新当前的token值
        // 3.返回access_token到客户端
        $rtn = $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS,
            ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SUCCESS),
            $this->responseWithToken($token));
        return response()->json($rtn);
    }

    protected function responseWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expired_at' => Carbon::now()->addSeconds(auth()->getTTL() * 60)->toDateTimeString(),
        ];
    }
}
