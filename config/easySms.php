<?php

return [

    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'yunpian', 'aliyun',
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'errorlog' => [
            'file' => storage_path('logs/sms/easy-sms.log'),
        ],
        'yunpian' => [
            //'api_key' => '824f0ff2f71cab52936axxxxxxxxxx',
        ],
        'aliyun' => [
            'access_key_id' => env('EASY_SMS_ALIYUN_ID'),
            'access_key_secret' => env('EASY_SMS_ALIYUN_SECRET'),
            'sign_name' => env('EASY_SMS_ALIYUN_SIGN'),
            'template_code' => env('EASY_SMS_ALIYUN_TEMPLATE'),
        ],
    ],
];
