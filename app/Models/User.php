<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable {
        notify as protected copyNotify;
    }

    protected $fillable = [
        'username', 'phone', 'email', 'email_verified_at', 'avatar', 'weixin_openid', 'weixin_unionid', 'introduction', 'notification_count',
        'password',
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getDates()
    {
        return $this->dates;
    }

    // 检查用户的基础执行权限，基础执行权限是指每个用户都有修改自己资料的权限
    public function own($model)
    {
        if ($model instanceof Authenticatable) {  // 判断参数 $model 是否当前用户模型
            return $this->id === $model->id;
        } else {
            return $this->id === $model->user_id;
        }
    }

    public function setPasswordAttribute($value)
    {
        if (mb_strlen($value, 'utf-8') != 60) {  // 检查密码有没有加密，如果没有加密就执行下面的逻辑
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }

    }

    // 设置avatar访问器
    public function getAvatarAttribute($value)
    {
        $avatar = 'https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/600/h/600';
        // 判断avatar为空，如果为空就使用默认头像
        if (empty($value)) {
            return $avatar;
        }
        // 如果图片存在，并且没有带http://或者https://，那就根据值获取资源模型里面的uri地址
        if (! preg_match('#^https?://.+$#i', $value)) {
            $resource = Resource::query()->where('id', $value)->public()->first();
            if (empty($resource)) {
                return $avatar;
            } // 如果没有找到对应的资源模型，那就使用默认的头像图片
            return Storage::disk('public')->url($resource->uri);
        }
        // 否则就返回它本身
        return $value;
    }

    /**
     * 删除用户头像文件
     * @param User $user
     */
    public function deleteAvatar()
    {
        // 判断头像文件，是否是本地文件，如果是就对头像文件进行删除
        $avatar = ltrim(str_replace(asset(Storage::url('')), '', $this->avatar, $count), '/');
        if (! preg_match('/^https?:\/\/.+$/i', $avatar)) {
            if ($count !== 0) {
                Storage::disk('public')->delete($avatar);  // 删除文件
            }
        }
    }

    public function notify($instance)
    {
        // 1.判断接收通知的用户是否等于当前用户，如果是就不执行通知
        if ($this->id === $instance->reply->user_id)
        {
            return;
        }
        // 2.如果有发送通知到database，那就更新notification_count
        if (in_array('database', (array) $instance->via($this)))
        {
            $this->increment('notification_count');
        }
        // 3.执行通知的业务逻辑
        $this->copyNotify($instance);
    }

    // 关联表
    // 文件资源关联表，一对多的关系
    public function resources()
    {
        return $this->hasMany(Resource::class, 'user_id', 'id');
    }
    // 话题关联表，一对多的关系
    public function topics()
    {
        return $this->hasMany(Topic::class, 'user_id', 'id');
    }
    // 回复关联表，一对多的关系
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

}
