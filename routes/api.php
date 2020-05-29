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
Route::prefix('v1')->name('api.v1.')->namespace('Api')->group(function (\Illuminate\Routing\Router $route) {

    $route->get('test', function (\App\Common\Utils\Utils $utils) {
        return $utils->toJson(0, ['name' => '小姐姐'], 404);
    })->name('test');

    // 发送短信认证
    $route->post('send-sms-verify', 'VerificationsController@sendSmsVerify')->middleware(['throttle:6,1'])->name('verifications.send_sms_verify');

    // socialite 授权认证
    $route->get('socialites/{socialite_type}/callback', 'SocialitesController@callback')->name('socialites.callback');

    // 注册登录
    // 1.微信注册登录
    $route->post('authorizations/{socialite_type}/socialite', 'AuthorizationsController@socialite')->name('authorizations.socialite');
    // 2.手册注册登录
    $route->post('authorizations/token', 'AuthorizationsController@token')->name('authorizations.token');

    // 用户接口
    $route->get('users/{user}', 'UsersController@show')->name('users.show');  // 查看用户个人信息

});

// 需要进行认证才能访问的接口
Route::prefix('v1')->name('api.v1.')->namespace('Api')
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
        // 修改用户信息
        $route->post('users/{user}', 'UsersController@update')->name('users.update');
        // 绑定手机号
        $route->post('users/{user}/set-phone', 'UsersController@setPhone')->name('users.set_phone');
        // 发送修改密码的短信验证
        $route->post('verifications/{user}/set-password-verify', 'Verifications@setPasswordVerify')->name('verifications.set_password_verify');
        // 设置用户密码
        $route->post('users/{user}/set-password', 'UsersController@setPassword')->name('users.set_password');
        // 设置用户邮箱号
        $route->post('users/{user}/set-email', 'UsersController@setEmail')->name('users.set_email');
        // 验证邮箱的回调地址
        $route->get('users/email-verify-callback', 'UsersController@emailVerifyCallback')->name('users.email_verify_callback');
        // 验证邮箱号
        $route->post('user/email-verify', 'UsersController@emailVerify')->name('users.email_verify');
});

