<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // 微信授权登录
    'weixin' => [
        'client_id' => env('SOCIALITE_WEIXIN_KEY'),
        'client_secret' => env('SOCIALITE_WEIXIN_SECRET'),
        'redirect' => env('SOCIALITE_WEIXIN_REDIRECT'),
    ],

    // 百度翻译
    'fanyi' => [
        'app_id' => env('BAIDU_FANYI_APP_ID'),
        'app_key' => env('BAIDU_FANYI_APP_KEY'),
        'request_url' => env('BAIDU_FANYI_REQUEST_URL', 'http://api.fanyi.baidu.com/api/trans/vip/translate?'),
    ],

    // 跨域请求配置
    'cross' => [
        'allow_origin' => env('CROSS_ALLOW_ORIGIN', null),
    ],

];
