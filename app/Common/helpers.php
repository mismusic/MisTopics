<?php

use App\Exceptions\ApiHandlerException;
use App\Common\ApiReturnCode;

// 助手类

/**
 * 返回一个api处理异常
 * @param $code
 * @param int $status
 * @return ApiHandlerException
 */
function api_error($code, int $status = 500) :ApiHandlerException
{
    throw new ApiHandlerException($code, ApiReturnCode::getReturnMessage($code), $status);
}

/**
 * 获取 api route prefix
 * @param string $join
 * @param string $version
 * @return string
 */
function get_api_prefix(string $join = '.', string $version = 'v1') :string
{
    return 'api' . $join . $version . $join;
}

/**
 * 根据主题内容来获取主题描述
 * @param string $content
 * @return string
 */
function topic_description(string $content, int $length = 200) :string
{
    $content = trim(strip_tags(str_replace(["\r\n", "\n", "\r", "\t", "\f"], '', $content)));  // 去掉里面的特殊字符串，html，php代码
    return mb_substr($content, 0, $length, 'utf-8');  // 从内容最前面截取一部分字符
}

/**
 * 后台路由名称前戳
 */
function admin_route_prefix($value)
{
    return config('admin.route.prefix') . '.' . $value;
}

function routeName()
{
    return str_replace('.', '-', \Illuminate\Support\Facades\Route::currentRouteName());
}

function paginate(array $data)
{
    array_push($data, [
        'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
        'pageName' => 'page',
    ]);
    return \Illuminate\Container\Container::getInstance(\Illuminate\Pagination\LengthAwarePaginator::class, $data);
}