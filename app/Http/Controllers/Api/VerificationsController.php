<?php

namespace App\Http\Controllers\Api;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use App\Common\Traits\Verifications;
use App\Common\Utils\Utils;
use App\Exceptions\ApiHandlerException;
use App\Http\Requests\Api\VerificationRequest;
use App\Jobs\SendVerifySms;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Overtrue\EasySms\EasySms;

class VerificationsController extends Controller
{
    use ResponseJson, Verifications;

    // 注册登录，或者设置手机号码的时候需要发送短信进行验证
    public function sendSmsVerify(Request $request, EasySms $easySms, Utils $utils)
    {
        // 1.请求表单验证
        $this->validate($request, [
            'phone' => [
                'required',
                'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
            ]
        ]);

        // 2. 通过阿里大于发送短信给用户
        $verificationData = $this->sendSms($easySms, $this->phone);
        // 返回短信验证的数据
        $rtn = $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS,
            ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SUCCESS),
            $verificationData);
        return response()->json($rtn);

    }

    // 设置密码的时候需要发送短信进行验证
    public function setPasswordVerify(User $user, EasySms $easySms, Utils $utils)
    {
        // 1.权限认证
        $this->authorize('own', $user);  // 检查当前用户是否有执行的权利
        // 检查用户手机号码是否存在，如果不存在就抛出错误
        if (! $user->phone) {
            throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_VERIFICATION_PHONE_NOT_EXISTS,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_VERIFICATION_PHONE_NOT_EXISTS), 401);
        }
        // 2.通过阿里云发送短信验证
        $verificationData = $this->sendSms($easySms, $user->phone);
        // 返回短信验证的数据
        $rtn = $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS,
            ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SUCCESS),
            $verificationData);
        return response()->json($rtn);
    }

}
