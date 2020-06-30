<?php

namespace App\Common;

use App\Exceptions\ApiHandlerException;

class ApiReturnCode
{

    // 定义api返回码
    // 全局返回码定义，范围(-1 - 1000)
    const API_RETURN_CODE_FAILED = -1;
    const API_RETURN_CODE_SUCCESS = 0;
    const API_RETURN_CODE_UNAUTHORIZED = 1;
    const API_RETURN_CODE_FORBIDDEN = 2;
    const API_RETURN_CODE_VALIDATOR_FAILED = 3;
    const API_RETURN_CODE_NOT_FOUND = 4;
    const API_RETURN_CODE_ERROR = 5;
    const API_RETURN_CODE_METHOD_NOT_ALLOWED = 6;

    // 文件错误码
    const API_RETURN_CODE_UNKNOWN_FILE_UNIT = 1000;
    const API_RETURN_CODE_FILE_SIZE_MUST_IS_NUMERIC = 1001;
    const API_RETURN_CODE_RESOURCE_NOT_EXISTS = 1002;
    const API_RETURN_CODE_FILE_MAX_NOT_ALLOWED = 1003;
    const API_RETURN_CODE_UPLOAD_FILE_FAILED = 1004;
    const API_RETURN_CODE_UPLOAD_FILE_SUCCESS = 1005;
    const API_RETURN_CODE_UPLOAD_FILE_PERMISSION_DENIED = 1006;
    const API_RETURN_CODE_UPLOAD_FILE_NOT_NULL = 1007;

    // 服务器错误码
    const API_RETURN_CODE_SERVER_ERROR = 2000;

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
    const API_RETURN_CODE_EMAIL_NOT_NULL = 10009;
    const API_RETURN_CODE_EMAIL_NOT_EXISTS_OR_VERIFIED = 10010;
    const API_RETURN_CODE_TYPE_ERROR = 10011;
    const API_RETURN_CODE_PHONE_NOT_FOUND = 10012;
    const API_RETURN_CODE_REQUEST_PHONE = 10013;
    const API_RETURN_CODE_PHONE_DIFFERENT = 10014;
    const API_RETURN_CODE_PHONE_EXISTS = 10015;

    // 定义回复的返回码，范围(13000 - 14000)
    const API_RETURN_CODE_TOPIC_NOT_EXISTS_REPLY = 13000;
    const API_RETURN_CODE_REPLY_EXISTS_SUB_NOT_DELETE = 13001;


    // 定义Api返回码和对应返回消息的Map，PHP7开始支持常量定义为数组的形式
    const API_RETURN_MAP = [
        // 全局返回码定义，范围(-1 - 1000)
        self::API_RETURN_CODE_FAILED => '当前操作失败',
        self::API_RETURN_CODE_SUCCESS => '返回数据成功',
        self::API_RETURN_CODE_UNAUTHORIZED => 'Token认证失败',
        self::API_RETURN_CODE_FORBIDDEN => '权限不足，拒绝执行当前操作',
        self::API_RETURN_CODE_VALIDATOR_FAILED => '请求参数验证失败',
        self::API_RETURN_CODE_NOT_FOUND => '页面不存在',
        self::API_RETURN_CODE_ERROR => '返回码不存在',
        self::API_RETURN_CODE_METHOD_NOT_ALLOWED => '请求方法不允许',

        // 文件错误码
        self::API_RETURN_CODE_UNKNOWN_FILE_UNIT => '未知的文件单位',
        self::API_RETURN_CODE_FILE_SIZE_MUST_IS_NUMERIC => '文件大小值必须是一个数字',
        self::API_RETURN_CODE_RESOURCE_NOT_EXISTS => '该资源类型不存在',
        self::API_RETURN_CODE_FILE_MAX_NOT_ALLOWED => '文件大小不允许',
        self::API_RETURN_CODE_UPLOAD_FILE_FAILED => '上传文件失败',
        self::API_RETURN_CODE_UPLOAD_FILE_SUCCESS => '上传文件成功',
        self::API_RETURN_CODE_UPLOAD_FILE_PERMISSION_DENIED => '权限不足，拒绝上传文件',
        self::API_RETURN_CODE_UPLOAD_FILE_NOT_NULL => '上传文件不能为空',

        // 服务器错误
        self::API_RETURN_CODE_SERVER_ERROR => '服务器内部错误',

        // 定义授权认证的返回码，范围(10000 - 11000)
        self::API_RETURN_CODE_CODE_INVALID => 'Code值已失效，请重新获取！',
        self::API_RETURN_CODE_GET_TOKEN_ERROR => '获取Token值失败',
        self::API_RETURN_CODE_PHONE_OR_PASSWORD_ERROR => '认证失败，手机号或密码错误',
        self::API_RETURN_CODE_VERIFICATION_KEY_INVALID => '短信验证码Key值不存在或已失效',
        self::API_RETURN_CODE_VERIFICATION_CODE_ERROR => '短信验证码错误',
        self::API_RETURN_CODE_VERIFICATION_PHONE_NOT_EXISTS => '用户还没有绑定手机号码',
        self::API_RETURN_CODE_VERIFICATION_EMAIL_EXPIRED => '该邮箱验证已过期，请在规定的时间内进行验证',
        self::API_RETURN_CODE_EMAIL_VERIFY_FAILED => '该邮箱验证失败',
        self::API_RETURN_CODE_USER_NOT_EXISTS => '该用户不存在',
        self::API_RETURN_CODE_EMAIL_NOT_NULL => '邮箱不能为空',
        self::API_RETURN_CODE_EMAIL_NOT_EXISTS_OR_VERIFIED => '该邮箱不存在或已验证',
        self::API_RETURN_CODE_TYPE_ERROR => '请求参数里面的类型错误',
        self::API_RETURN_CODE_PHONE_NOT_FOUND => '找不到该手机号码',
        self::API_RETURN_CODE_REQUEST_PHONE => '请求参数里面必须带上手机号码',
        self::API_RETURN_CODE_PHONE_DIFFERENT => '发送短信的手机号和要修改的用户手机号不一致',
        self::API_RETURN_CODE_PHONE_EXISTS => '该手机号已存在',

        // 定义回复的返回码，范围(13000 - 14000)
        self::API_RETURN_CODE_TOPIC_NOT_EXISTS_REPLY => '主题下面不存在该回复',
        self::API_RETURN_CODE_REPLY_EXISTS_SUB_NOT_DELETE => '该回复下面存在子回复，无法进行删除',

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