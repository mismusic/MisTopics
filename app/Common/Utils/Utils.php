<?php

namespace App\Common\Utils;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use App\Exceptions\ApiHandlerException;
use Illuminate\Support\Str;

class Utils {

    use ResponseJson;

    // 生成一个随机的数字字符串
    public function getRandomNumCode($length = 6)
    {
        $code = '';
        for ($i = 0; $i < $length; $i ++) {
            $randNum = random_int(0, 9);
            $code .= $randNum;
        }
        return $code;
    }

    public function convertFileSize($fileSize)
    {
        // 1.初始化单位大小
        $k = 1024;  // 单位KB
        $m = $k * $k;  // 单位MB
        $g = $m * $k;  // 单位G
        $t = $g * $k;  // 单位T

        // 2.获取到文件的单位值
        $unit = 'B';
        if (preg_match('/^(\d+)([a-zA-Z]+)$/', $fileSize, $match)) {
            $fileSize = $match[1];  // 获取文件大小，不包括单位值
            $unit = $match[2];  // 根据正则表达式来匹配到对应的单位值
        }

        // 3.检查文件大小是否为一个数值
        if (! is_numeric($fileSize)) {
            throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_FILE_SIZE_MUST_IS_NUMERIC,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_FILE_SIZE_MUST_IS_NUMERIC), 422);
        }

        // 4.把不同的单位大小，都转化为统一的单位B
        switch (strtoupper($unit)) {
            case substr($unit,0, 1) === 'B':
                break;
            case substr($unit,0, 1) === 'K':
                $fileSize *= $k;
                break;
            case substr($unit,0, 1) === 'M':
                $fileSize *= $m;
                break;
            case substr($unit,0, 1) === 'G':
                $fileSize *= $g;
                break;
            case substr($unit,0, 1) === 'T':
                $fileSize *= $t;
                break;
            default:
                throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_UNKNOWN_FILE_UNIT,
                    ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_UNKNOWN_FILE_UNIT), 422);
                break;
        }

        // 5.自动把文件大小转化为一个合适的单位值
        if ($fileSize >= $t) {
            $fileSize = round($fileSize / $t, 2) . 'T';
        } else if ($fileSize >= $g) {
            $fileSize = round($fileSize / $g, 2) . 'G';
        } else if ($fileSize >= $m) {
            $fileSize = round($fileSize / $m, 2) . 'MB';
        } else if ($fileSize >= $k) {
            $fileSize = round($fileSize / $k, 2) . 'KB';
        } else {
            $fileSize .= 'B';
        }

        // 6.然后返回文件大小
        return $fileSize;

    }

    /**
     * 生成一个邮箱验证的token值
     * @param array $data
     * @param string $join
     * @return string
     */
    public function getEmailVerifyToken(array $data, $join = '-') :string
    {
        $sign = md5($data['email'] . $join . $data['time']) . $join . $data['state'];
        // 进行hmac-sha256加密
        $token = hash_hmac('sha256', $sign, config('app.key'));
        return $token;
    }

    public function toJson($code, $data = [], $status = 200)
    {
        $rtn = $this->returnJson($code, ApiReturnCode::getReturnMessage($code), $data);
        return response()->json($rtn, $status);
    }

}