<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 不需要进行认证就能访问的接口
Route::prefix('v1')
    ->name('api.v1.')
    ->namespace('Api')
    ->group(function (\Illuminate\Routing\Router $route) {
        $route->get('test', function () {
            $user = \App\Models\User::query()->paginate();
            dd($user);
        })->name('test');  // 测试接口

        // 1.认证接口
        // 发送短信认证
        $route->post('send-sms-verify', 'VerificationsController@sendSmsVerify')->middleware(['throttle:6,1'])->name('verifications.send_sms_verify');
        // socialite 授权认证
        $route->get('socialites/{socialite_type}/callback', 'SocialitesController@callback')->name('socialites.callback');
        // --1.微信注册登录
        $route->post('authorizations/{socialite_type}/socialite', 'AuthorizationsController@socialite')->name('authorizations.socialite');
        // --2.手册注册登录
        $route->post('authorizations/token', 'AuthorizationsController@token')->name('authorizations.token');

        // 2.用户接口
        $route->get('users/{user}', 'UsersController@show')->name('users.show');  // 查看用户个人信息
        // 验证邮箱的回调地址
        $route->get('users/email/verify/callback', 'UsersController@emailVerifyCallback')->name('users.email_verify_callback');
        // 验证用户邮箱
        $route->post('users/email/verify', 'UsersController@emailVerify')->name('users.email_verify');
        // 找回密码
        $route->patch('users/forgot/password', 'UsersController@forgotPassword')->name('users.forgot_password');
        // 用户的话题列表
        $route->get('users/{user}/topics', 'UsersController@topics')->name('users.topics');
        // 用户的回复列表
        $route->get('users/{user}/replies', 'UsersController@replies')->name('users.replies');

        // 3.分类接口
        $route->get('categories', 'CategoriesController@index')->name('categories.index');
        $route->get('categories/{category}', 'CategoriesController@show')->name('categories.show');

        // 4.主题接口
        $route->apiResource('topics', 'TopicsController')->only(['index', 'show']);

});

// 需要进行认证才能访问的接口
Route::prefix('v1')
    ->name('api.v1.')
    ->namespace('Api')
    ->middleware(['auth:api'])
    ->group(function (\Illuminate\Routing\Router $route) {
        // 1.验证接口
        // 删除 token
        $route->delete('authorizations/logout', 'AuthorizationsController@logout')->name('authorizations/logout');
        // 刷新 token
        $route->patch('authorizations/refresh', 'AuthorizationsController@refresh')->name('authorizations/refresh');

        // 2.用户接口
        // 查看当前用户信息
        $route->get('users/current/user', 'UsersController@user')->name('users.user');
        // 用户通知列表
        $route->get('notifications', 'UsersController@notifications')->name('users.notifications');
        // 删除用户通知
        $route->delete('notifications/{notification}', 'UsersController@notificationDestroy')->name('user.notification_destroy');
        // 修改用户信息
        $route->post('users/{user}', 'UsersController@update')->name('users.update');
        // 设置用户手机号，密码，邮箱
        $route->patch('users/{user}/set', 'UsersController@set')->name('users.set');

        // 3.话题接口
        $route->apiResource('topics', 'TopicsController')->only(['store', 'update', 'destroy']);

        // 回复接口
        $route->post('topics/{topic}/reply', 'TopicsController@replyStore')->name('topics.reply_store');
        $route->delete('topics/{topic}/reply/{reply}', 'TopicsController@replyDestroy')->name('topics.reply_destroy');

        // 上传文件
        $route->post('resources', 'ResourcesController@store')->name('resources.store');

});

// 默认路由
Route::fallback(function (\Illuminate\Http\Request $request) {
    if ($request->expectsJson()) {
        api_error(\App\Common\ApiReturnCode::API_RETURN_CODE_NOT_FOUND, 404);
    } else {
        abort(404);
    }
});

