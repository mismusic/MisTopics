<?php

namespace App\Http\Requests\Api;


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
                if (Route::currentRouteName() === 'api.v1.users.updates') {
                    return [
                        'username' => 'required|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-_]+$/u|between:1,25',
                        'introduction' => 'nullable|required|between:1,255',
                        'avatar' => 'nullable|file|mimes:jpg,jpeg,png|dimensions:min_width=100,min_height=100',
                    ];
                } else if ($this->is('api/v1/users/*/set-phone')) {
                    return [
                        'phone' => [
                            'required',
                            'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                            'unique:users',
                        ],  // 验证手机号码是否规范
                        'verification_key' => ['required'],  // 短信验证码key值
                    ];
                } else if ($this->is('api/v1/users/*/set-password')) {
                    return [
                        'phone' => [
                            'required',
                            'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                        ],  // 验证手机号码是否规范
                        'verification_key' => ['required'],  // 短信验证码key值
                        'password' => 'required|alpha_dash|between:6,15',  // 密码的验证规则
                    ];
                } else if (Route::currentRouteName() === 'api.v1.users.email_verify') {
                    return [
                        'email' => 'required|email',
                        'time' => 'required|integer',
                        'state' => 'required|string',
                        'token' => 'required|string',
                    ];
                }
        }
    }

    public function attributes()
    {
        return [
            'introduction' => '用户简介',
            'verification_key' => '短信验证码key值',
        ];
    }

    public function withValidator(Validator $validator)
    {
        switch (strtoupper($this->method())) {
            case 'POST';
                // 判断文件是否存在，如果存在就执行下面逻辑
                if ($this->hasFile('avatar')) {
                    $validator->after(function (Validator $validator) {
                        if ($this->avatar->getSize() > $this->avatar->getMaxFilesize()) {
                            $validator->errors()->add('avatar', '文件大小不能超过服务器配置的最大上传文件的大小');
                        }
                    });
                }
                break;
        }
    }
}
