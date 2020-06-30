<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');

    // 后台用户接口
    $router->resource('users', 'UsersController');

    // 后台分类接口
    $router->resource('categories', 'CategoriesController');

    // 后台话题接口
    $router->post('upload-file', 'TopicsController@uploadFile')->name('topics.upload_file');  // 上传文件
    $router->resource('topics', 'TopicsController');

    // 后台回复接口
    $router->resource('replies', 'RepliesController')->only(['show', 'index', 'update']);

    // 后台资源接口
    $router->resource('resources', 'ResourcesController')->except(['show', 'created', 'store']);

});
