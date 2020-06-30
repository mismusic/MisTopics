<?php

namespace App\Http\Requests\Api;


use Illuminate\Validation\Validator;

class AuthorizationRequest extends Request
{

    const SOCIALITE_WEIXIN_AUTHORIZATION = 'weixin';

    public $socialiteTypeMap = [
        self::SOCIALITE_WEIXIN_AUTHORIZATION,
    ];  // 允许授权登录的方式列表

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // 根据请求路径来判断是微信登录注册还是手机登录注册
        if ($this->is('api/v1/authorizations/*/socialite')) {
            // 微信登录注册的处理逻辑
            return [
                'code' => ['required', 'string'],
            ];
        } else if($this->is('api/v1/authorizations/token')) {
            // 手机登录注册的处理逻辑
            return [
                'verification_code' => ['required_without:password'],  // 当请求参数 passowrd 不存在的时候那这个字段必须要存在
                'verification_key' => ['required_with:verification_code'],  // 只有当请求参数 verification_code 存在时，这个字段才必须要写
                'password' => ['required_without:verification_code'],  // 当请求参数 verification_code 不存在的时候，那这个字段必须要写
                'phone' => [
                    'required_with:password',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                ],  // 验证手机号码是否规范
            ];
        }
    }

    public function withValidator(Validator $validator) {
        // 当通过第三方认证服务器授权登录时才执行的逻辑
        if ($this->is('api/v1/authorizations/*/socialite')) {
            $validator->after(function (Validator $validator) {
                // 判断第三方授权登录的方式是否是被允许的，如果不被允许就返回一个错误信息
                if (!in_array($this->route('socialite_type'), $this->socialiteTypeMap)) {
                    $validator->errors()->add('socialite_type', '不允许通过该认证服务器进行授权登录');
                }
            });
        }
    }

    public function attributes()
    {
        return [
            'verification_code' => '短信验证码',
            'verification_key' => '短信验证码key值',
        ];
    }

}
