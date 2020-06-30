<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'PagesController@index')->name('pages.index');

// 用户路由
Route::get('login', 'UsersController@login')->name('users.login');  // 用户登录
Route::get('users/{user}', 'UsersController@show')->name('users.show');  // 用户资料
Route::get('users/{user}/topics', 'UsersController@topics')->name('users.topics');  // 用户的话题列表
Route::get('users/{user}/replies', 'UsersController@replies')->name('users.replies');  // 用户的回复列表
Route::get('users/{user}/edit', 'UsersController@edit')->name('users.edit');  // 编辑用户资料
Route::get('users/{user}/set', 'UsersController@set')->name('users.set');  // 用户设置
