<?php

namespace App\Admin\Extensions\Export;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExporter extends ExcelExporter implements WithMapping
{
    protected $fileName = '用户列表.xlsx';

    protected $columns = [
        'id' => 'ID',
        'username' => '用户名',
        'phone' => '手机号',
        'email' => '邮箱',
        'email_verified_at' => '邮箱验证时间',
        'weixin_openid' => '微信 openid',
        'weixin_unionid' => '微信 unionid',
        'notification_count' => '通知数',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
    ];

    public function map($user) :array
    {
        return [
            $user->id,
            $user->username,
            $user->phone,
            $user->email,
            $user->email_verified_at ? '已验证' : '未验证',
            $user->weixin_openid,
            $user->weixin_unionid,
            $user->notification_count,
            $user->created_at,
            $user->updated_at,
        ];
    }

}