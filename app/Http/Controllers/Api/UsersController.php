<?php

namespace App\Http\Controllers\Api;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use App\Common\Traits\Verifications;
use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\NotificationCollection;
use App\Http\Resources\ReplyResource;
use App\Http\Resources\TopicCollection;
use App\Http\Resources\TopicResource;
use App\Http\Resources\UserResource;
use App\Models\Queries\UserQuery;
use App\Models\Resource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Http;

class UsersController extends Controller
{
    use ResponseJson, Verifications;

    /**
     * @apiDefine UserNotFoundError
     *
     * @apiError (错误参数) {String} API_RETURN_CODE_USER_NOT_EXISTS 该用户不存在
     *
     * @apiErrorExample {json} 错误示例:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "code": 10008,
     *       "msg": "该用户不存在"
     *     }
     */

    /**
     * @apiDefine ResponseData
     * @apiSuccess (返回数据) {Number} id                              User ID
     * @apiSuccess (返回数据){String} username                        用户名
     * @apiSuccess (返回数据){String} phone                           手机号码
     * @apiSuccess (返回数据){String} email                           邮箱号码
     * @apiSuccess (返回数据){String} email_verified_at               邮箱验证时间
     * @apiSuccess (返回数据){String} avatar                          用户头像
     * @apiSuccess (返回数据){String} weixin_openid                   微信openid
     * @apiSuccess (返回数据){String} weixin_unionid                  微信unionid
     * @apiSuccess (返回数据){String} introduction                    用户简介
     * @apiSuccess (返回数据){Object[]} replies                         用户回复信息
     * @apiSuccess (返回数据){Number} replies.id                      回复 ID
     * @apiSuccess (返回数据){Number} replies.pid                     回复 parent ID
     * @apiSuccess (返回数据){String} replies.content                 回复内容
     * @apiSuccess (返回数据){String} replies.created_at              回复创建时间
     * @apiSuccess (返回数据){String} replies.updated_at              回复更新时间
     * @apiSuccess (返回数据){String} created_at                      用户创建时间
     * @apiSuccess (返回数据){String} updated_at                      用户更新时间
     */

    /**
     * @api {get} /api/v1/users/:id 获取用户个人信息
     * @apiVersion 1.0.0
     * @apiName GetUsers
     * @apiGroup 用户接口
     *
     * @apiParam (请求参数) {Integer} id    用户ID
     * @apiParam (请求参数) {String} [include]  需要包含的关联模型，例如include=topics,replies.topic
     * @apiParamExample {String} 请求示例:
     *      /api/v1/users/1?include=topics,replies.topic
     *
     * @apiUse ResponseData
     * @apiSuccessExample {json} 响应示例:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *              "id": 1,
     *              "username": "Mis",
     *              "phone": "13534256342",
     *              "email": "2543205432@qq.com",
     *              "email_verified_at": null,
     *              "avatar": "https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/600/h/600",
     *              "weixin_openid": null,
     *              "weixin_unionid": null,
     *              "introduction": "Labore et omnis voluptatem ut nostrum.",
     *              "replies": [
     *                  {
     *                      "id": 64,
     *                      "pid": 24,
     *                      "content": "Minus dolores asperiores ut autem libero facere.",
     *                      "created_at": "2周前",
     *                      "updated_at": "2周前"
     *                  },
     *                  {
     *                      "id": 68,
     *                      "pid": 0,
     *                      "content": "Modi rem magnam sint autem quibusdam inventore ea.",
     *                      "created_at": "4周前",
     *                      "updated_at": "4周前"
     *                  },
     *              ],
     *              "created_at": "2020-05-30 16:10:38",
     *              "updated_at": "2020-06-10 00:13:59"
     *          },
     *          "code": 200,
     *          "msg": "返回数据成功"
     *      }
     *
     * @apiUse UserNotFoundError
     */
    public function show(Request $request, $id, UserService $userService)
    {
        $user = $userService->show($request, $id);  // 获取用户信息
        return new UserResource($user);  // 返回数据
    }

    public function topics(Request $request, User $user, UserService $userService)
    {
        $topics = $userService->topics($request, $user);
        return new TopicCollection($topics);
    }

    public function replies(Request $request, User $user, UserService $userService)
    {
        $replies = $userService->replies($request, $user);
        return ReplyResource::collection($replies);
    }

    public function user(Request $request)
    {
        $user = $request->user();  // 获取当前用户信息
        return new UserResource($user);  // 返回当前用户模型
    }

    public function notifications(Request $request, UserService $userService)
    {
        $notifications = $userService->notifications($request);  // 执行用户通知列表逻辑
        return new NotificationCollection($notifications);  // 返回用户通知列表
    }

    public function notificationDestroy(DatabaseNotification $notification, UserService $userService)
    {
        $this->authorize('own', $notification);  // 检查权限
        $userService->notificationDestroy($notification);  // 删除当前用户下面的通知数据
        return $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS);  // 返回数据
    }

    public function update(UserRequest $request, User $user, Resource $resource, UserService $userService)
    {
        // 1.验证用户是否有权限执行该操作
        $this->authorize('update', $user);
        // 2.更新用户信息
        $user = $userService->update($request, $user, $resource);
        // 3.返回用户模型
        return new UserResource($user);
    }

    public function set(UserRequest $request, User $user, UserService $userService)
    {
        // 1.检查是否有权限执行
        $this->authorize('own', $user);
        // 2.设置用户信息
        $user = $userService->set($request, $user);
        // 3.返回用户模型数据
        return new UserResource($user);
    }

    public function emailVerifyCallback(Request $request)
    {
        $requestData = [
            'email' => $request->query('email'),
            'time' => $request->query('time'),
            'state' => $request->query('state'),
            'token' => $request->query('token'),
        ];
        // 像邮箱号验证接口发送http请求
        $responseData = Http::asForm()->post(route(get_api_prefix() . 'users.email_verify', $requestData))->json();
        return response()->json($responseData);
    }

    public function emailVerify(UserRequest $request, UserService $userService)
    {
        // 1.对邮箱进行验证
        $user = $userService->emailVerify($request);
        // 2.返回用户模型
        return new UserResource($user);
    }

    public function forgotPassword(UserRequest $request, UserService $userService)
    {
        // 1.短信验证，验证通过后，进行密码的修改
        $user = $userService->forgotPassword($request);
        // 2.返回用户模型
        return new UserResource($user);
    }


}
