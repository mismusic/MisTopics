<?php

namespace App\Http\Controllers\Api;

use App\Common\ApiReturnCode;
use App\Common\Traits\ResponseJson;
use App\Common\Traits\Verifications;
use App\Common\Utils\UploadFile;
use App\Common\Utils\Utils;
use App\Exceptions\ApiHandlerException;
use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendEmailVerify;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    use ResponseJson, Verifications;

    const EMIAL_VERIFY_TTL = 5;  // 单位表示分钟

    // todo
    public function show($id)
    {
        $user = User::query()->where('id', $id)->first();
        if (empty($user)) {
            api_error(ApiReturnCode::API_RETURN_CODE_USER_NOT_EXISTS, 404);
        }
        return new UserResource($user);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return new UserResource($user);
    }

    public function update(UserRequest $request, User $user, Resource $resource)
    {
        // 验证用户是否有权限执行该操作
        $this->authorize('update', $user);
        // 1.获取请求数据，并且组装要写入用户表的数据
        $username = $request->input('username');  // 用户名
        $introduction = $request->input('introduction');  // 用户简介
        // 把要存储到用户表的数据都写入到一个数组里面
        $data = [
            'username' => $username,
            'introduction' => $introduction,
        ];
        // 2.检查有没有上传头像，如果上传了就获取对应的文件信息，并且把它存储到资源表中
        $resourceId = null;
        if ($request->hasFile('avatar'))
        {
            // 生成一个头像文件，并且根据参数对头像进行缩放和剪切
            $fileInfo = UploadFile::localStorage($request->file('avatar'), 'images', $user->id, 'image', 250);
            // 对以前的头像文件进行删除
            $this->deleteAvatar($user);
            // 把文件信息写入资源表
            $resource = $resource->fill([
                'type' => $fileInfo['type'],
                'name' => $fileInfo['fileName'],
                'original_name' => $fileInfo['fileOriginalName'],
                'uri' => $fileInfo['fileUri'],
            ]);  // 批量填充数据到资源模型
            $resource->user()->associate($user->id);  // 把用户id写入到resource模型
            $resource->save();  // 保存数据到资源表
            $resourceId = $resource->id;  // 获取创建的资源id
        }
        // 3.把要修改的数据写入到用户表
        if ($resourceId) {
            $data['avatar'] = $resourceId;  // 如果资源id存在，就把avatar的值设置为资源的id
        }
        $user->update($data);  // 更新用户信息
        // 4.返回用户信息
        return new UserResource($user);
    }

    public function setPhone(UserRequest $request, User $user)
    {
        // 1.检查是否有权限执行
        $this->authorize('own', $user);
        // 2.进行短信验证
        $this->smsVerify($request);
        // 进行手机号的绑定
        $user->update([
            'phone' => $request->phone,
        ]);
        // 返回当前用户的信息
        return new UserResource($user);
    }

    public function setPassword(UserRequest $request, User $user)
    {
        // 1.检查是否有权限执行
        $this->authorize('own', $user);
        // 2.进行短信验证
        $this->smsVerify($request);
        // 修改密码
        $user->update([
            'password' => $request->password,
        ]);
        // 返回当前用户的信息
        return new UserResource($user);
    }

    // 设置用户邮箱号
    public function setEmail(Request $request, User $user, Utils $utils)
    {
        // 检查执行权限
        $this->authorize('own', $user);
        // 表单验证
        $this->validate($request, [
            'email' => ['required', 'email', 'unique:uses,email'],  // 邮箱号必须是唯一的
        ]);
        // 设置邮箱号
        $user->update([
            'email' => $request->email,
        ]);
        // 发送邮件进行验证
        $emailVerifyData = [
            'email' => $user->email,
            'time' => time(),
            'state' => Str::random(10),
        ];
        // 获取邮箱验证的token值
        $token = $utils->getEmailVerifyToken($emailVerifyData);
        // 通过任务队列来发送邮件验证
        $emailVerifyData['token'] = $token;
        $uri = route('users/email-verify-callback', $emailVerifyData);
        $sendData = [
            'username' => $user->username,
            'email' => $user->email,
            'subject' => config('app.name') . '，邮箱号验证',
            'uri' => $uri,
        ];
        dispatch(new SendEmailVerify($sendData));
        // 返回数据
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
        $responseData = Http::asForm()->post(route('users/email-verify', $requestData))->json();
        $rtn = $this->returnJson(ApiReturnCode::API_RETURN_CODE_SUCCESS,
            ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SUCCESS), $responseData);
        return response()->json($rtn);
    }

    public function emailVerify(UserRequest $request, Utils $utils)
    {
        // 检查该token的验证时间是否过期
        $expiredAt = Carbon::createFromTimestamp($request->time)->addSeconds(self::EMIAL_VERIFY_TTL);
        // 如果时间过期了，那邮箱验证就会无效，必须在规定的时间内进行邮箱验证
        if (time() > $expiredAt) {
            throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_VERIFICATION_EMAIL_EXPIRED,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_VERIFICATION_EMAIL_EXPIRED), 401);
        }
        // 根据请求参数来生成token值
        $data = [
            'email' => $request->email,
            'time' => $request->time,
            'state' => $request->state,
        ];
        $token = $utils->getEmailVerifyToken($data);
        // 检查token是否一致
        if (! hash_equals($token, $request->token)) {
            throw new ApiHandlerException(ApiReturnCode::API_RETURN_CODE_EMAIL_VERIFY_FAILED,
                ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_EMAIL_VERIFY_FAILED), 401);
        }
        // 验证成功，进行邮箱验证时间的修改
        $user = User::query()->where('email', $request->email)->first();
        $user->update([
            'email_verified_at' => now()->toDateTimeString(),
        ]);
        // 返回数据
        return new UserResource($user);
    }

    private function deleteAvatar(User $user)
    {
        // 判断头像文件，是否是本地文件，如果是就对头像文件进行删除
        if (! preg_match('/^https?:\/\/.+$/i')) {
            $fullPath = storage_path('app/public/' . $user->avatar);
            if (file_exists($fullPath)) {  // 检查头像文件是否存在，如果存在就进行删除
                Storage::delete($fullPath);  // 删除文件
            }
        }
    }

}
