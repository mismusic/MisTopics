<?php

namespace App\Common\Traits;

// 响应json类型的数据
trait ResponseJson
{
    /**
     * 返回一个标准规范的api接口数据
     * @param $msg  返回消息
     * @param int $code 返回码
     * @param array $data  返回的数据
     * @return array
     */
    public function returnJson(int $code, string $msg, array $data = []) :array
    {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
    }
}