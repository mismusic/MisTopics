<?php

namespace App\Http\Controllers\Api;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use App\Common\Utils\Utils;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TopicResource;
use App\Models\Category;
use App\Models\Queries\TopicQuery;

class CategoriesController extends Controller
{

    use ResponseJson;

    public function index()
    {
        $categories = Category::query()->sort()->get();  // 获取所有分类数据
        $categories = Utils::getTreeDataIteration($categories);  // 对数据进行关系父子的关系进行排列
        // 返回分类数据
        return $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS, $categories);
    }

    public function show($id, TopicQuery $query)
    {
        $topics = $query->subTreeData($id)->paginate(16);
        return TopicResource::collection($topics);
    }

}
