<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    public function login()
    {
        return view('users.login', ['title' => '登录页面']);
    }

    public function show($id)
    {
        $title = '用户资料';
        return view('users.show', compact(['title', 'id']));
    }

    public function topics(User $user)
    {
        $title = '用户话题列表';
        $topics = $user->topics()->order('create')->with(['category'])->paginate(16);
        return view('users.topics', compact(['title', 'user', 'topics']));
    }

    public function replies(User $user)
    {
        $replies = $user->replies()->paginate();
        $title = '用户回复列表';
        return view('users.replies', compact(['title', 'user', 'replies']));
    }

    public function edit(User $user)
    {
        $title = '修改用户信息';
        return view('users.edit', compact(['title', 'user']));
    }

    public function set(User $user)
    {
        $type = request('type');
        if (! $type || ! in_array($type, ['phone', 'password', 'email'])) {
            abort(404);
        }
        $title = '用户设置';
        return view('users.set', compact(['title', 'user']));
    }

}
