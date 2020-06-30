<?php

namespace App\Services;

use App\Common\ApiReturnCode;
use App\Common\Traits\Verifications;
use App\Common\Utils\UploadFile;
use App\Common\Utils\Utils;
use App\Jobs\SendEmailVerify;
use App\Models\Queries\UserQuery;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserService
{

    use Verifications;

    const EMAIL_VERIFY_TTL = 30;  // 单位表示分钟
    const SET_TYPE_PHONE = 'phone';
    const SET_TYPE_PASSWORD = 'password';
    const SET_TYPE_EMAIL = 'email';
    const RESOURCE_TYPE_IMAGE = 'image';  // 资源类型是image
    const RESOURCE_TYPE_FILE = 'file';  // 资源类型是file

    public static $uploadImageFolder = 'images';  // 上传头像的文件夹
    public static $uploadFileFolder = 'files';  // 上传文件的文件夹
    public static $avatarSize = 250;  // 头像尺寸，宽高都是这个值，单位px

    /**
     * 获取用户信息
     * @param Request $request
     * @param UserQuery $query
     * @param int $id
     * @return User
     */
    public function show(Request $request, int $id) :User
    {
        $user = User::query()->where('id', $id)->with(['topics', 'replies.topic'])->first();  // 根据用户id来获取对应的用户信息
        if (empty($user)) {
            api_error(ApiReturnCode::API_RETURN_CODE_USER_NOT_EXISTS, 404);
        }
        $user->topics = $user->topics()->order()->limit(8)->get();
        $user->replies = $user->replies()->order()->limit(8)->get();
        return $user;
    }

    public function topics(Request $request, User $user)
    {
        $perPage = $request->query('per_page');  // 获取每页显示多条数据
        $topics = $user->topics()->with('user', 'category')->order()->paginate($perPage);  // 查询出用户下面的话题数据，并且进行分页
        return $topics;  // 返回话题数据集
    }

    public function replies(Request $request, User $user)
    {
        $perPage = $request->query('per_page');  // 获取每页显示多条数据
        $replies = $user->replies()->with('user', 'topic')->order()->paginate($perPage);  // 查询用户下的回复数据，并且进行分页
        return $replies;
    }

    /**
     * 设置用户邮箱
     * @param Request $request
     * @param User $user
     * @return User
     */
    public function setEmail(Request $request, User $user) :User
    {
        // 1.如果执行出错就对事务进行回滚
        DB::transaction(function () use ($request, $user) {
            // 设置用户邮箱
            $user->update([
                'email' => $request->email,
            ]);
            // 发送邮件验证
            $this->sendEmail($request, $user);
        });
        // 2.返回用户模型
        return $user;
    }

    /**
     * 发送邮件验证
     * @param Request $request
     * @param User $user
     */
    public function sendEmail(Request $request, User $user) :void
    {
        // 检查邮箱是否为空，如果为空就返回错误
        if (! $user->email) {
            api_error(ApiReturnCode::API_RETURN_CODE_EMAIL_NOT_NULL);
        }
        // 发送邮件进行验证
        $emailVerifyData = [
            'email' => $user->email,
            'time' => time(),  // 获取当前的时间戳
            'state' => Str::random(10),  // 生成一个随机字符串
        ];  // 用来生成token的数据
        $token = Utils::getEmailVerifyToken($emailVerifyData);  // 获取邮箱验证的token值
        $emailVerifyData['token'] = $token;  // 把token放到query参数里面
        $uri = route(get_api_prefix() . 'users.email_verify_callback', $emailVerifyData);  // 生成用来验证邮箱的uri链接
        $sendData = [
            'username' => $user->username,
            'email' => $user->email,
            'subject' => config('app.name') . '，邮箱验证',
            'uri' => $uri,
            'expires_in' => self::EMAIL_VERIFY_TTL,
        ];
        // 通过指派任务队列来发送邮件
        dispatch(new SendEmailVerify($sendData));
    }

    /**
     * 邮箱验证
     * @param Request $request
     * @return User
     */
    public function emailVerify(Request $request) :User
    {
        // 1.检查该token的验证时间是否过期
        $expiredAt = Carbon::createFromTimestamp($request->time)->addMinutes(self::EMAIL_VERIFY_TTL)->timestamp;  // 获取验证时间过期的最大时间戳
        // 如果时间过期了，那邮箱验证就会无效，必须在规定的时间内进行邮箱验证
        if (time() > $expiredAt) {  // 当前时间戳大于过期的最大时间戳，就表示邮箱验证已经失效了
            api_error(ApiReturnCode::API_RETURN_CODE_VERIFICATION_EMAIL_EXPIRED, 403);
        }
        $data = [
            'email' => $request->email,
            'time' => $request->time,
            'state' => $request->state,
        ];
        $token = Utils::getEmailVerifyToken($data);  // 根据请求参数来生成token值
        // 检查token是否一致
        if (! hash_equals($token, $request->token)) {
            api_error(ApiReturnCode::API_RETURN_CODE_EMAIL_VERIFY_FAILED, 403);
        }
        // 2.对用户邮箱进行验证
        $user = User::query()->where(['email' => $request->email])->whereNull('email_verified_at')->first();
        // 当该邮箱不存在或者已验证的时候就返回错误
        if (! $user) {
            api_error(ApiReturnCode::API_RETURN_CODE_EMAIL_NOT_EXISTS_OR_VERIFIED, 403);
        }
        // 更新邮箱验证的时间戳，表示该邮箱已被验证
        $user->update([
            'email_verified_at' => now()->toDateTimeString(),
        ]);
        // 3.返回用户模型
        return $user;
    }

    /**
     * 设置用户信息（phone，password，email）
     * @param Request $request
     * @param User $user
     * @return User
     */
    public function set(Request $request, User $user)
    {
        $type = strtolower($request->input('type'));  // 获取请求参数里面的类型
        if ($type && $type !== self::SET_TYPE_EMAIL) {  // 当类型不等于email的时候，就进行短信验证
            $verificationData = $this->smsVerify($request);  // 短信验证
        }
        if ($type === self::SET_TYPE_PHONE) {  // 设置用户手机号时执行的逻辑

            // 检查要修改的用户手机号是否存在
            if ($user->phone) {
                if ($verificationData['phone'] != $user->phone) {  // 修改用户的手机号必须和发送短信验证码的手机号码一致
                    api_error(ApiReturnCode::API_RETURN_CODE_PHONE_DIFFERENT);
                }
                $phone = $request->input('phone');
                // 如果请求参数里面没有phone，就报错
                if (! $phone) {
                    api_error(ApiReturnCode::API_RETURN_CODE_REQUEST_PHONE);
                }
            } else {
                $phone = $verificationData['phone'];
            }
            if (User::query()->where('phone', $phone)->first(['id'])) {  // 如果手机号已经存在用户表，那就返回错误
                api_error(ApiReturnCode::API_RETURN_CODE_PHONE_EXISTS);
            }
            // 进行手机号的绑定
            $user->update([
                'phone' => $phone,
            ]);
        }else if ($type === self::SET_TYPE_PASSWORD) {  // 设置用户密码时执行的逻辑
            if ($user->phone) {  // 当用户手机号存在的时候，就验证手机号码是否正确
                if ($verificationData['phone'] != $user->phone) {  // 修改用户的手机号必须和发送短信验证码的手机号码一致
                    api_error(ApiReturnCode::API_RETURN_CODE_PHONE_DIFFERENT);
                }
            } else {  // 当用户手机号不存在是，提示用户先绑定手机号才能进行密码的修改
                api_error(ApiReturnCode::API_RETURN_CODE_VERIFICATION_PHONE_NOT_EXISTS);
            }
            // 设置用户密码
            $user->update([
                'password' => bcrypt($request->password),  // 必须加密密码才能写入到用户表
            ]);
        } else if ($type === self::SET_TYPE_EMAIL) {  // 设置用户邮箱是执行的逻辑
            $user = $this->setEmail($request, $user); // 修改用户表里面的邮箱，并且发送邮件进行验证
        }
        // 返回用户模型
        return $user;
    }

    /**
     * 更新用户信息（username，introduction，avatar）
     * @param Request $request
     * @param User $user
     * @param Resource $resource
     * @return User
     */
    public function update(Request $request, User $user, Resource $resource)
    {
        // 1.获取请求数据，并且组装要写入用户表的数据
        $username = $request->input('username');  // 用户名
        $introduction = $request->input('introduction');  // 用户简介
        // 把要存储到用户表的数据都写入到一个数组里面
        $data = [
            'username' => $username,
            'introduction' => $introduction,
        ];
        // 2.检查有没有上传头像，如果上传了就获取对应的文件信息，并且把它存储到资源表中
        if ($request->hasFile('avatar'))
        {
            $resource = $this->uploadFile($request, $user, $resource, self::$uploadImageFolder, self::RESOURCE_TYPE_IMAGE, self::$avatarSize);  // 上传文件，并且把文件信息保存到对应的资源模型表中，然后返回对应的资源模型
        }
        // 3.把要修改的数据写入到用户表
        if ($resource) {
            $data['avatar'] = $resource->id;  // 如果资源id存在，就把avatar的值设置为资源的id
        }
        $user->update($data);  // 更新用户信息
        // 返回用户模型
        return $user;
    }

    /**
     * 上传文件，并把文件信息保存到资源模型表
     * @param Request $request
     * @param User $user
     * @param Resource $resource
     * @param $folder
     * @param $type
     * @param int $size
     * @return Resource|bool
     */
    public function uploadFile(Request $request, User $user, Resource $resource, $folder, $type, $size = 200)
    {
        if ($type === self::RESOURCE_TYPE_IMAGE) {
            // 生成一个头像文件，并且根据参数对头像进行缩放和剪切
            $fileInfo = UploadFile::localStorage($request->file('avatar'), $folder, $user->id, $type, $size);
            // 对以前的头像文件进行删除
            $user->deleteAvatar();
        } else if ($type === self::RESOURCE_TYPE_FILE) {
            $fileInfo = UploadFile::localStorage($request->file('avatar'), $folder, $user->id, $type);  // 上传文件
        } else {  // 当资源类型不存在的时候抛出错误
            api_error(ApiReturnCode::API_RETURN_CODE_RESOURCE_NOT_EXISTS);
        }
        // 把文件信息写入资源表
        $resource = $resource->fill([
            'type' => $fileInfo['type'],  // 资源类型
            'name' => $fileInfo['fileName'],  // 资源名称
            'original_name' => $fileInfo['fileOriginalName'], // 源文件名
            'uri' => $fileInfo['fileUri'],  // 资源uri存储地址
        ]);  // 批量填充数据到资源模型
        $resource->user()->associate($user->id);  // 把用户id写入到resource模型
        if ($resource->save()) {  // 保存数据到资源表
            return $resource;  // 如果保存资源数据成功，就返回资源模型
        }
        return false;  // 如果保存资源数据失败，就返回 false
    }

    /**
     * 找回密码
     * @param Request $request
     * @return User
     */
    public function forgotPassword(Request $request) :User
    {
        $verificationData = $this->smsVerify($request);  // 进行短信验证
        $user = User::query()->where('phone', $verificationData['phone'])->first();  // 根据手机号查询出对应的用户模型
        if (empty($user)) {  // 当找不到手机号对应的用户模型时，返回一个错误
            api_error(ApiReturnCode::API_RETURN_CODE_PHONE_NOT_FOUND);  // 找不到该手机号码
        }
        // 进行密码的修改
        $user->password = bcrypt($request->input('password'));
        // 保存数据到用户表
        $user->save();
        // 返回该用户模型
        return $user;
    }

    public function notifications(Request $request)
    {
        $user = auth()->user();  // 获取当前用户模型
        $this->markNotificationRead($user);  // 标记当前用户未读的通知为已读
        $perPage = $request->query('per_page');  // 获取每一页多少条数据
        $notifications = auth()->user()->notifications()->paginate($perPage);  // 获取当前用户分页后的通知数据
        return $notifications;  // 返回通知数据集
    }

    public function notificationDestroy(DatabaseNotification $notification)
    {
        $notification->delete();  // 删除当前用户下面的通知数据
    }

    public function markNotificationRead(User $user) :void
    {
        $user->unreadNotifications->markAsRead();  // 标记未读的通知为已读
        $user->update(['notification_count' => 0]);  // 修改用户表里面的 notifications_count 为 0
    }


}