<?php

namespace App\Common\Traits;

use App\Common\ApiReturnCode;
use App\Common\Utils\Utils;
use App\Exceptions\ApiHandlerException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Overtrue\EasySms\EasySms;

trait Verifications
{

    public function smsVerify(Request $request) :void
    {
        $phone = $request->phone;
        $verificationData = Cache::get($request->input('verification_key'));
        // 如果获取的验证信息为null，就抛出错误
        if (empty($verificationData)) {
            throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_VERIFICATION_KEY_INVALID,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_VERIFICATION_KEY_INVALID), 401);
        }
        // 判断请求参数里面的phone和verification_code是否和存储在缓存里面的验证数据一致
        if ($verificationData['phone'] !== $phone || $verificationData['verification_code'] !== $request->verification_code)
        {
            throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_VERIFICATION_CODE_ERROR,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_VERIFICATION_CODE_ERROR), 401);
        }
    }

    public function sendSms(EasySms $easySms, $phone)
    {
        $utils = new Utils();
        $code = $utils->getRandomNumCode();
        $smsData = [
            'easySms' => $easySms,
            'phone' => $phone,
            'code' => $code,
        ];
        // 通过队列的方式发送短信给用户
        SendVerifySms::dispatch($smsData);

        // 把手机验证码存储到缓存
        $key = 'verification_' . Str::random();
        $value = [
            'verification_code' => $code,
            'phone' => $phone,
        ];
        $ttl = now()->addMinutes(5);
        Cache::put($key, $value, $ttl);

        // 返回缓存的key值，和缓存的过期时间
        $data = [
            'verification_key' => $key,
            'expired_at' => $ttl->toDateTimeString(),
        ];
    }

}