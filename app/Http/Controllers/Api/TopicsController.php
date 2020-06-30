<?php

namespace App\Http\Controllers\Api;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use App\Http\Requests\Api\TopicRequest;
use App\Http\Resources\TopicCollection;
use App\Http\Resources\TopicResource;
use App\Models\Queries\TopicQuery;
use App\Models\Reply;
use App\Http\Resources\ReplyResource;
use App\Models\Topic;
use App\Services\TopicService;
use Illuminate\Http\Request;

class TopicsController extends Controller
{

    use ResponseJson;

    public function index(Request $request, TopicQuery $query)
    {
        $perPage = (int) $request->query('per_page');
        $topics = $query->paginate($perPage);  // 获取所有主题数据，并且进行分页
        // 返回主题模型数据
        return new TopicCollection($topics);
    }

    public function store(TopicRequest $request, Topic $topic, TopicService $topicService)
    {
        if (! $topicService->store($request, $topic))
        {
            return $this->returnJson(ApiReturnCode::API_RETURN_CODE_FAILED, [], 500);
        }
        return $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS);
    }

    public function show(Request $request, Topic $topic, TopicService $topicService)
    {
        $topic = $topicService->show($request, $topic);
        $topic = $topic->except('deleted_at');
        return $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS, $topic);  // 返回数据
    }

    public function update(TopicRequest $request, Topic $topic, TopicService $topicService)
    {
        $this->authorize('own', $topic);  // 权限验证
        if (! $topicService->update($request, $topic))
        {
            return $this->returnJson(ApiReturnCode::API_RETURN_CODE_FAILED, [], 500);
        }
        return $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS);
    }

    public function destroy(Topic $topic)
    {
        $this->authorize('own', $topic);  // 权限验证
        $topic->delete();
        return $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS);
    }

    public function replyStore(TopicRequest $request, Topic $topic, TopicService $topicService)
    {
        // 添加回复，并且触发事件通知
        $reply = $topicService->replyStore($request, $topic);
        if (is_null($reply)) {
            api_error(ApiReturnCode::API_RETURN_CODE_FAILED);
        }
        // 返回响应数据
        return new ReplyResource($reply);
    }

    public function replyDestroy(Topic $topic, Reply $reply, TopicService $topicService)
    {
        // 检查权限
        $this->authorize('own', $reply);
        $topicService->replyDestroy($topic, $reply);  // 执行删除回复的逻辑
        // 返回数据
        return $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS);
    }

}
