<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'username', 'phone', 'email', 'avatar', 'weixin_openid', 'weixin_unionid', 'introduction', 'notification_count',
        'password',
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // 检查用户的基础执行权限，基础执行权限是指每个用户都有修改自己资料的权限
    public function own($user)
    {
        return $this->id === $user->id;
    }

    // 设置avatar访问器
    public function getAvatarAttribute($value)
    {
        // 判断avatar为空，如果为空就使用默认头像
        if (empty($value)) {
            return 'https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/600/h/600';
        }
        // 如果图片存在，并且没有带http://或者https://，那就根据值获取资源模型里面的uri地址
        if (! preg_match('#^https?://.+$#i', $value)) {
            $resource = Resource::query()->where('id', $value)->first();
            if (empty($resource)) {
                return 'https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/600/h/600';
            } // 如果没有找到对应的资源模型，那就使用默认的头像图片
            return asset(Storage::url($resource->uri));
        }
        // 否则就返回它本身
        return $value;
    }

    // 用户表和资源的表的关系，一对多
    public function resources()
    {
        return $this->hasMany(Resource::class, 'user_id', 'id');
    }

}
