<?php

namespace App\Services;

use App\Common\ApiReturnCode;
use App\Common\Utils\UploadFile;
use App\Common\Utils\Utils;
use App\Events\CreateReply;
use App\Models\Reply;
use App\Models\Resource;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class TopicService
{

    public function show(Request $request, Topic $topic)
    {
        $topic->recordReadCount();  // 记录下每次访问主题的阅读数
        $includes = explode(',', $request->query('include'));  // 关联模型名列表，根据逗号来划分为一个数组
        $perPage = $request->query('per_page');  // 每页显示多少条数据，默认等于15
        $replies = $topic->replies()->with('user')->get(['id', 'pid', 'content', 'user_id', 'created_at']);  // 获取当前话题的所有回复数据
        $topLevelReplies = $topic->replies()->where('pid', 0)->paginate($perPage, ['id']);  // 先从数据库里面查出当前话题的所有顶级回复，然后进行分页
        $replies = $replies->keyBy('id');  // 把回复id作为数组的key
        $replies = $replies->map(function (Reply $reply) {
            $reply->user = $reply->user->only('id', 'username', 'avatar');
            $reply->unsetRelation('user');
            unset($reply->user_id);
            return $reply;
        })->toArray();  // 过滤一下回复列表里面的每个回复模型，并且把数据集转换为一个数组
        // 根据顶级回复找出自己下面的子回复，并且把顶级回复和子回复放在同一个数组里面
        $result = [];
        foreach ($topLevelReplies as $k => $topLevelReply) {  // 循环所有的顶级回复id
            if (isset($replies[$topLevelReply['id']])) {
                $treeData = Utils::getTreeDataIteration($replies, $topLevelReply['id']);
                if ($treeData) {
                    $replies[$topLevelReply['id']]['children'] = $treeData;  // 把所有子回复都放在顶级回复的元素children里面，以一个数组的形式
                }
                array_push($result, $replies[$topLevelReply['id']]);  // 根据顶级回复的id找到对应的顶级回复模型，并且把这个模型写入到数组里面
            }
        }
        // 检查有没有预加载关联名 user，category
        $relations = 'user,category';
        foreach ($includes as $include) {
            if (Utils::checkRelations($include, $relations))
            {
                switch (strtolower($include)) {
                    case 'user':
                        $topic->user = $topic->user->only('id', 'username', 'avatar');  // 查询出话题关联的用户模型需要取出的字段
                        $topic->unsetRelation('user');  // 删掉话题的用户关联
                        unset($topic->user_id);
                        break;
                    case 'category':
                        $topic->category = $topic->category->only('id', 'name');  // 查询出话题关联的分类模型需要取出的字段
                        $topic->unsetRelation('category');
                        unset($topic->category_id);
                        break;
                }
            }
        }
        $data = $topLevelReplies->toArray();  // 把回复分页转化为一个数组
        $data['data'] = $result;  // 用排序好的回复列表覆盖data下面的数据
        $topic->replies = $data;  // 把回复信息列表添加到topic模型的属性replies里面
        return $topic;
    }

    public function store(Request $request, Topic $topic)
    {
        $data = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ];
        $topic->fill($data);
        $topic->user()->associate($request->user()->id);
        $topic->category()->associate($request->input('category_id'));
        return $topic->save();  // 保存主题模型到数据表
    }

    public function update(Request $request, Topic $topic)
    {
        $topic->category()->associate($request->input('category_id'));  // 把分类Id填充到主题模型的属性上面
        return $topic->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ]);  // 进行主题数据的更新
    }

    public function replyStore(Request $request, Topic $topic)
    {
        $reply = $topic->replies()->create([
            'pid' => $request->input('pid'),
            'content' => $request->input('content'),
            'user_id' => $request->user()->id,
        ]);  // 把回复数据写入的数据表
        if (! $reply) {
            return null;  // 添加主题回复失败，返回null
        }
        event(new CreateReply($reply));  // 通过添加回复来触发通知事件
        return $reply;  // 返回当前的回复模型数据
    }

    public function replyDestroy(Topic $topic, Reply $reply)
    {
        $reply = $topic->replies()->where('id', $reply->id)->first();
        if (empty($reply)) {  // 判断该主题下面的回复模型是否存在
            api_error(ApiReturnCode::API_RETURN_CODE_TOPIC_NOT_EXISTS_REPLY);
        }
        $replies = $topic->replies()->get(['id', 'pid']);  // 获取当前主题下面的所有回复数据的id，pid
        $subTreeIds = Utils::getSubTreeIds($replies, $reply->id);  // 获取当前回复下面的所有子回复id（包括当前回复的id）
        array_shift($subTreeIds);  // 删掉当前回复的id
        if ($subTreeIds) {  // 判断改回复下面是否还有子回复，如果有就无法删除该回复
            api_error(ApiReturnCode::API_RETURN_CODE_REPLY_EXISTS_SUB_NOT_DELETE);
        }
        $reply->delete();  // 删除该回复信息
    }

    public function uploadFile(Request $request, Resource $resource)
    {
        $user = $request->user();  // 获取当前用户信息
        $fileInfo = UploadFile::localStorage($request->file('file'), 'files', $user->id, 'file');  // 获取当前上传文件的信息
        // 把文件信息写入到资源表
        $resource->fill([
            'type' => $fileInfo['type'],
            'name' => $fileInfo['fileName'],
            'original_name' => $fileInfo['fileOriginalName'],
            'uri' => $fileInfo['fileUri'],
        ]);  // 批量填充数据到资源模型
        $resource->user()->associate($user->id);  // 把用户id填充到资源模型
        if (! $resource->save())  // 保存数据到数据表
        {
            return [
                'success' => false,
                'msg' => ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_UPLOAD_FILE_FAILED),
                'file_path' => '',
            ];
        }
        return [
            'success' => true,
            'msg' => ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_UPLOAD_FILE_SUCCESS),
            'file_path' => asset(Storage::url($fileInfo['fileUri'])),  // 网站访问路径
        ];
    }


}