<?php

namespace App\Http\Requests\Api;


use App\Common\ApiReturnCode;
use Illuminate\Validation\Rule;

class VerificationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ((int) $this->input('type') === 1) {  // 注册、登录、设置手机号时需要发送的短信验证
            return [
                'phone' => [
                    'required',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                ],
            ];
        } else if ((int) $this->input('type') === 2) {  // 设置密码、找回密码时需要发送短信验证
            return [
                'phone' => [
                    'required',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                    Rule::exists('users', 'phone'),  // 检查该手机号码是否已经存在于用户表中
                ]
            ];
        } else {
            return [
                'type' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $fail(ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_TYPE_ERROR));
                    }
                ]
            ];
        }
    }

    public function attributes()
    {
        return [
            'type' => '类型',
        ];
    }

    public function messages()
    {
        return [
            'phone.exists' => '找不到该手机号',
        ];
    }
}
