<?php

namespace App\Http\Controllers\Api;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use App\Common\Traits\Verifications;
use App\Http\Requests\Api\VerificationRequest;
use Overtrue\EasySms\EasySms;

class VerificationsController extends Controller
{
    use ResponseJson, Verifications;

    // 注册登录，或者设置手机号码的时候需要发送短信进行验证
    public function sendSmsVerify(VerificationRequest $request, EasySms $easySms)
    {
        // 1.通过阿里大于发送短信给用户
        $verificationData = $this->sendSms($easySms, $request->phone);
        // 2.返回短信验证的数据
        return $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS, $verificationData);
    }


}
