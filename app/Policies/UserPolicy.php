<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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

    // 更新权限
    public function update(User $currentUser, User $user)
    {
        return $currentUser->own($user);
    }

    // 基础权限
    public function own(User $currentUser, User $user)
    {
        return $currentUser->own($user);
    }

}
