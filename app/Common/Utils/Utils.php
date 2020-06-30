<?php

namespace App\Common\Utils;

use App\Common\ApiReturnCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Overtrue\Pinyin\Pinyin;

class Utils {

    const TREE_DATA_MERGE = 'merge';
    const TREE_DATA_CHILDREN = 'children';

    // 生成一个随机的数字字符串
    public static function getRandomNumCode($length = 6)
    {
        $code = '';
        for ($i = 0; $i < $length; $i ++) {
            $randNum = random_int(0, 9);
            $code .= $randNum;
        }
        return $code;
    }

    public static function convertFileSize($fileSize)
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
            api_error(ApiReturnCode::API_RETURN_CODE_FILE_SIZE_MUST_IS_NUMERIC);
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
               api_error(ApiReturnCode::API_RETURN_CODE_UNKNOWN_FILE_UNIT);
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
    public static function getEmailVerifyToken(array $data, $join = '-') :string
    {
        $sign = md5($data['email'] . $join . $data['time']) . $join . $data['state'];
        // 进行hmac-sha256加密
        $token = hash_hmac('sha256', $sign, config('app.key'));
        return $token;
    }

    /**
     * 把一个数据集合转化为一个数组类型
     * @param $data
     * @return array
     */
    public static function collectToArray($data) :array
    {
        if (is_object($data) && method_exists($data, 'toArray')) {  // 当$data是一个对象，并且该对象里面有方法toArray的时候，执行下面逻辑
            $data = $data->toArray();
        }
        return $data;
    }

    /**
     * 把一个数组进行树形结构排序（使用递归的方式来实现），数据排序的方式有两种：1.merge 2.children
     * @param array $data
     * @param int $id
     * @param int $level
     * @param string $type
     * @param string $column
     * @param string $childrenName
     * @return array
     */
    public static function getTreeData($data, int $id = 0, $level = 1, $type = 'merge', $column = 'pid', $childrenName = 'children', string $join = '----') :array
    {
        $treeData = [];
        foreach ($data as $k => $value) {
            if ($value[$column] === $id) {
                unset($data[$k]);
                $value['level'] = $level;
                $nextLevel = $level + 1;
                if ($type === self::TREE_DATA_MERGE) {
                    if (isset($value['name'])) {
                        $value['name'] = ($join ? '|' : '') . str_repeat($join, $value['level']) . $value['name'];
                    }
                    $treeData[] = $value;
                    $childrenData = self::getTreeData($data, $value['id'], $nextLevel);
                    $treeData = array_merge($treeData, $childrenData);  // 对父子数组进行合并
                } else if ($type === self::TREE_DATA_CHILDREN) {
                    $childrenData = self::getTreeData($data, $value['id'], $nextLevel, self::TREE_DATA_CHILDREN);
                    if ($childrenData) {
                        $value[$childrenName] = $childrenData;  // 当子数组不为空时，把子数组作为父数组里面key为$childrenName的值
                    }
                    $treeData[] = $value;
                } else {
                    api_error(ApiReturnCode::API_RETURN_CODE_TYPE_ERROR);
                }
            }

        }
        return $treeData;  // 返回按照树形结构排序以后的数据
    }

    /**
     * 把一个数组进行树形结构排序（使用迭代的方式来实现）数据排序的方式是：merge
     * @param array $data
     * @param int $id
     * @param int $level
     * @param string $column
     * @return array
     */
    public static function getTreeDataIteration($data, int $id = 0, int $level = 1, $column = 'pid', $join = '----') :array
    {
        $pids = [$id];  // 父id列表
        $treeData = [];  // 要返回的数组
        while ($pids) {  // 判断父id列表是否为空，如果为空就停止循环
            $pid = end($pids);  // 获取父id列表中最后一个元素
            $isDelete = true;  // 默认值为true，当$data数组为空的时候，会删除$pids里面多余的值
            foreach ($data as $k => $value) {
                if ((int) $value[$column] === (int) $pid) {  // 判断该值里面的pid是不是等于父id列表里面的最后一个元素，如果是就进行入栈操作
                    $value['level'] = $level;  // 写入层次到当前数据里面
                    if (isset($value['name'])) {
                        $value['name'] = ($join ? '|' : '') . str_repeat($join, $value['level']) . $value['name'];
                    }
                    $treeData[] = $value;  // 把当前数据写入到要返回的数组里面
                    array_push($pids, $value['id']);  // 入栈，把当前id写入到父id列表中
                    unset($data[$k]);  // 删除当前数组的值，避免下次循环时找到的还是这个数据
                    $level ++;  // 在当前层次上面加一
                    $isDelete = false;  // 设置值为false
                    break 1;  // 退出当前循环
                }
            }
            if ($isDelete) {  // 判断是否进行出栈操作，如果该值等于true，就会进行出栈操作
                array_pop($pids);  // 出栈，删除当前父id列表中的最后一个元素
                $level --; // 在当前层次上面减一
            }
        }
        return $treeData;  // 返回按照树形结构排序以后的数据
    }

    /**
     * 把一个数组进行树形结构排序（使用 递归 + 引用 的方式来实现）数据排序的方式是：children
     * @param array $data
     * @param int $id
     * @param int $level
     * @param string $column
     * @param string $childrenName
     * @return array
     */
    public static function getTreeDataRef($data, int $id = 0, int $level = 1, $column = 'pid', $childrenName = 'children') :array
    {
        $data = self::collectToArray($data);
        $data = array_column($data, null, 'id');  // 把数组的key换成二维数组里面的列名为id的值
        $treeData = [];
        foreach ($data as $k => $value)
        {
            if ((int) $value[$column] === (int) $id)
            {
                $data[$k]['level'] = $level;
                $treeData[] = & $data[$k];
            }
            else if (isset($data[$value[$column]]))  // 当该值的pid为$data里面的key时,执行下面逻辑
            {
                if (isset($data[$value[$column]]['level']))
                {
                    $data[$k]['level'] = $data[$value[$column]]['level'] + 1;  // 对层级加一
                    $data[$value[$column]][$childrenName][] = & $data[$k];  // 把子数据追加到父数据的children数组里面
                }
            }
        }
        return $treeData;  // 返回按照树形结构排序以后的数据
    }

    /**
     * 获取当前id下面的所有子id列表（列表中包括当前id）
     * @param array $data
     * @param int $id
     * @param string $column
     * @return array
     */
    public static function getSubTreeIds($data, int $id, $column = 'pid')
    {
        $data = self::collectToArray($data);
        $subTreeIds = [$id];
        $levelIds = $subTreeIds;
        while (true) {
            $levelIdsCopy = [];
            foreach ($data as $k => $value) {
                if (in_array($value[$column], $levelIds)) {
                    $levelIdsCopy[] = $value['id'];  // 存储子数据的id
                    unset($data[$k]);  // 删除已经进行存储的数据
                }
            }
            if (! $levelIdsCopy) {
                break 1;  // 如果当前层次获取不到对应的id值，就表示数据排序已经到底，该退出while循环了
            }
            $levelIds = $levelIdsCopy;
            $subTreeIds = array_merge($subTreeIds, $levelIds);
        }
        return $subTreeIds;  // 返回该id下面的所有子数据的id组成的数组
    }

    /**
     * 百度翻译
     * @param string $content
     * @return mixed|string
     * @throws \Exception
     */
    public static function translate(string $content)
    {
        // 1.判断是否配置了百度翻译 app_id app_key 这些，如果没有就是用拼音翻译
        $appId = config('services.fanyi.app_id');
        $appKey = config('services.fanyi.app_key');
        $requestUrl = config('services.fanyi.request_url');
        if (! $appId || ! $appKey || ! $requestUrl) {
            return self::pinyin($content);  // 使用拼音翻译
        } else {
            $q = $content;  // 要被翻译的内容
            $from = 'zh';  // 要翻译之前的语言类型（auto表示自动检测语言类型）
            $to = 'en';  // 要被翻译成什么样的语言类型（en英文）
            $salt = random_int(10000000, 99999999);  // 生成一个随机数用作盐值
            // 生成签名，规则（md5(appId + q + salt + appKey)）
            $sign = md5($appId . $q . $salt . $appKey);
            $queryString = http_build_query([
                'q' => urlencode($q),  // 对请求内容进行urlencode加密
                'from' => $from,
                'to' => $to,
                'appid' => $appId,
                'salt' => $salt,
                'sign' => $sign,
            ]);
            $url = $requestUrl . $queryString;  // 拼接成一个完整的请求url地址
            // 2.发送get请求
            $result = Http::get($url)->json();
            dd($result);
            // 3.判断请求是否成功，如果请求出现错误，就使用拼音翻译
            if (array_key_exists('error_code', $result)) {
                return self::pinyin($content);
            }
            // 4.输出百度翻译后的内容
            return Str::slug($result['trans_result'][0]['dst']);  // 获取翻译后的英文的内容，并且对内容进行slug转换
        }
    }

    /**
     * 生成用于链接的拼音字符串
     * @param $content
     * @return mixed
     */
    public static function pinyin($content)
    {
        return app(Pinyin::class)->permalink($content);  // 把中文翻译成为拼音并且用 - 拼接起来
    }

    /**
     * 检查请求参数中带的相对关联
     * @param $value
     * @param $relations
     * @return bool
     */
    public static function checkRelations($value, $relations) :bool
    {
        $value = strtolower(trim($value));
        $relations = explode(',', strtolower(trim($relations)));
        foreach ($relations as $relation)
        {
            if ($value === $relation)
            {
                return true;
            }
            $relation = explode('.', $relation);
            if (in_array($value, $relation))
            {
                return true;
            }
        }
        return false;
    }

}