<?php

namespace App\Policies;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TopicPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function own(User $user, Topic $topic)
    {
        return $user->own($topic);  // 检查当前用户的基础权限（如果这个主题是该用户创建的，那就有权进行更新和删除，这就是基础权限）
    }
}
