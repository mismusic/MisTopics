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

Route::get('/', function () {
    $user = \App\Models\User::create([
        'openid' => 'xxdfdf',
        'avatar' => 'http://thirdwx.qlogo.cn/mmopen/vi_32/ZtIIiclNZCcV6tQrdcW3CFgiclqTBfDKJEBjA0NWFxuzNHbnpazbwtUXElRuTPy7HmqeE68sOTibvXRyQVjtouiaEA/132',
        'name' => '天空之城'
    ]);
});
