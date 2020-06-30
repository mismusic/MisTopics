<?php

namespace App\Http\Requests\Api;


use App\Common\ApiReturnCode;
use App\Services\UserService;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Validator;

class UserRequest extends Request
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
        switch (strtoupper($this->method())) {
            case 'POST':
                if (Route::currentRouteName() === get_api_prefix() . 'users.updates') {
                    return [
                        'username' => 'required|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-_]+$/u|between:1,25|unique:users,username,' . $this->route('user')->id,
                        'introduction' => 'required|between:1,255',
                        'avatar' => 'file|mimes:jpg,jpeg,png|dimensions:min_width=100,min_height=100',
                    ];
                } else if (Route::currentRouteName() === get_api_prefix() . 'users.email_verify') {  // 邮箱验证
                    return [
                        'email' => 'required|email',
                        'time' => 'required|integer',
                        'state' => 'required|string',
                        'token' => 'required|string',
                    ];
                } else if (Route::currentRouteName() === get_api_prefix() . 'users.forgot_password') {
                    return [
                        'verification_key' => ['required'],  // 短信验证码key值
                        'verification_code' => ['required'],  // 短信验证码
                        'password' => 'required|alpha_dash|between:6,15',  // 密码的验证规则
                    ];
                }
            case 'PATCH':
                if (Route::currentRouteName() === get_api_prefix() . 'users.set') {
                    switch (strtolower($this->input('type'))) {
                        case UserService::SET_TYPE_PHONE:
                            return [
                                'phone' => [
                                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                                ],
                                'verification_key' => ['required'],  // 短信验证码key值
                                'verification_code' => ['required'],  // 短信验证码
                            ];
                            break;
                        case UserService::SET_TYPE_PASSWORD:
                            return [
                                'verification_key' => ['required'],  // 短信验证码key值
                                'verification_code' => ['required'],  // 短信验证码
                                'password' => 'required|alpha_dash|between:6,15',  // 密码的验证规则
                            ];
                            break;
                        case UserService::SET_TYPE_EMAIL:
                            return [
                                'email' => ['required', 'email', 'unique:users,email'],  // 邮箱号必须是唯一的
                            ];
                            break;
                        default:
                            return [
                                'type' => [
                                    'required',
                                    function ($attribute, $value, $fail) {
                                        $fail(ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_TYPE_ERROR));  // 返回一个表单验证错误
                                    }
                                ]
                            ];
                            break;
                    }
                }
        }
    }

    public function attributes()
    {
        return [
            'type' => '类型',
            'introduction' => '用户简介',
            'verification_key' => '短信验证码key值',
            'verification_code' => '短信验证码',
        ];
    }

    public function withValidator(Validator $validator)
    {
        switch (strtoupper($this->method())) {
            case 'POST';
                if (Route::currentRouteName() === get_api_prefix() . 'users.updates') {
                    // 判断文件是否存在，如果存在就执行下面逻辑
                    if ($this->hasFile('avatar')) {
                        $validator->after(function (Validator $validator) {
                            if ($this->avatar->getSize() > $this->avatar->getMaxFilesize()) {
                                $validator->errors()->add('avatar', '文件大小不能超过服务器配置的最大上传文件的大小');
                            }
                        });
                    }
                }
                break;
        }
    }
}
