<?php

namespace App\Common\Traits;

// 响应json类型的数据
use App\Common\ApiReturnCode;
use App\Common\Utils\Utils;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

trait ResponseJson
{
    /**
     * 返回一个标准规范的api接口数据
     * @param $msg  返回消息
     * @param int $code 返回码
     * @param array $data  返回的数据
     * @return array
     */
    public function toResponse(int $code, string $msg, array $data = []) :array
    {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
    }

    /**
     * 返回一个Json类型的api接口数据
     * @param $code
     * @param array $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function returnJson(int $code = 0, $data = [], int $status = 200) :JsonResponse
    {
        $data = Utils::collectToArray($data);
        $rtn = $this->toResponse($code, ApiReturnCode::getReturnMessage($code), $data);
        return response()->json($rtn, $status);
    }

}