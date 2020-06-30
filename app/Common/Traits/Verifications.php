<?php

namespace App\Common\Traits;

use App\Common\ApiReturnCode;
use App\Common\Utils\Utils;
use App\Jobs\SendVerifySms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Overtrue\EasySms\EasySms;

trait Verifications
{

    public function smsVerify(Request $request) :array
    {
        $verificationData = Cache::get($request->input('verification_key'));
        Cache::forget($request->input('verification_key'));  // 删除缓存里面的短信验证码信息
        // 如果获取的验证信息为null，就抛出错误
        if (empty($verificationData)) {
            api_error(ApiReturnCode::API_RETURN_CODE_VERIFICATION_KEY_INVALID, 401);
        }
        // 判断请求参数里面的phone和verification_code是否和存储在缓存里面的验证数据一致
        if (! hash_equals($verificationData['verification_code'], $request->verification_code))
        {
            api_error(ApiReturnCode::API_RETURN_CODE_VERIFICATION_CODE_ERROR, 401);
        }
        return $verificationData;
    }

    public function sendSms(EasySms $easySms, $phone) :array
    {
        $code = Utils::getRandomNumCode();
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
        return $data;
    }

}