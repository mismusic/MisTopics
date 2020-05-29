<?php

namespace App\Common;

use App\Exceptions\ApiHandlerException;

class ApiReturnCode
{

    // 定义api返回码
    // 全局返回码定义，范围(-1 - 1000)
    const API_RETURN_CODE_ERROR = -1;
    const API_RETURN_CODE_SUCCESS = 0;
    const API_RETURN_CODE_TOKNE_AUTHENTICATION_ERROR = 1;
    const API_RETURN_CODE_UNAUTHORIZED = 2;

    // 文件错误码
    const API_RETURN_CODE_UNKNOWN_FILE_UNIT = 1000;
    const API_RETURN_CODE_FILE_SIZE_MUST_IS_NUMERIC = 1001;

    // 服务器错误码
    const API_RETURN_CODE_SERVER_ERROR = 500;

    // 定义授权认证的返回码，范围(10000 - 11000)
    const API_RETURN_CODE_CODE_INVALID = 10000;
    const API_RETURN_CODE_GET_TOKEN_ERROR = 10001;
    const API_RETURN_CODE_PHONE_OR_PASSWORD_ERROR = 10002;
    const API_RETURN_CODE_VERIFICATION_KEY_INVALID = 10003;
    const API_RETURN_CODE_VERIFICATION_CODE_ERROR = 10004;
    const API_RETURN_CODE_VERIFICATION_PHONE_NOT_EXISTS = 10005;
    const API_RETURN_CODE_VERIFICATION_EMAIL_EXPIRED = 10006;
    const API_RETURN_CODE_EMAIL_VERIFY_FAILED = 10007;
    const API_RETURN_CODE_USER_NOT_EXISTS = 10008;


    // 定义Api返回码和对应返回消息的Map，PHP7开始支持常量定义为数组的形式
    const API_RETURN_MAP = [
        // 全局返回码定义，范围(-1 - 1000)
        self::API_RETURN_CODE_ERROR => '返回码不存在',
        self::API_RETURN_CODE_SUCCESS => '返回数据成功',
        self::API_RETURN_CODE_TOKNE_AUTHENTICATION_ERROR => 'Token认证失败',
        self::API_RETURN_CODE_UNAUTHORIZED => '权限不足，拒绝执行当前操作',

        // 文件错误码
        self::API_RETURN_CODE_UNKNOWN_FILE_UNIT => '未知的文件单位',
        self::API_RETURN_CODE_FILE_SIZE_MUST_IS_NUMERIC => '文件大小值必须是一个数字',

        // 服务器错误
        self::API_RETURN_CODE_SERVER_ERROR => '服务器内部错误',

        // 定义授权认证的返回码，范围(10000 - 11000)
        self::API_RETURN_CODE_CODE_INVALID => 'Code值已失效，请重新获取！',
        self::API_RETURN_CODE_GET_TOKEN_ERROR => '获取Token值失败',
        self::API_RETURN_CODE_PHONE_OR_PASSWORD_ERROR => '认证失败，手机号或密码错误',
        self::API_RETURN_CODE_VERIFICATION_KEY_INVALID => '短信验证码Key值不存在或已失效',
        self::API_RETURN_CODE_VERIFICATION_CODE_ERROR => '短信验证码错误或已失效',
        self::API_RETURN_CODE_USER_NOT_EXISTS => '该用户不存在',
        self::API_RETURN_CODE_VERIFICATION_PHONE_NOT_EXISTS => '用户还没有绑定手机号码，无法进行短信验证',
        self::API_RETURN_CODE_VERIFICATION_EMAIL_EXPIRED => '该邮箱验证已过期，请在规定的时间内进行验证',
        self::API_RETURN_CODE_EMAIL_VERIFY_FAILED => '该邮箱号验证失败',

    ];

    public static function getReturnMessage(int $code)
    {
        // 如果返回码不存在，就抛出一个错误
        if (! array_key_exists($code, self::API_RETURN_MAP)) {
            throw new ApiHandlerException(self::API_RETURN_CODE_ERROR, self::getReturnMessage(self::API_RETURN_CODE_ERROR));
        }
        // 返回返回码对应的返回消息
        return self::API_RETURN_MAP[$code];
    }


}