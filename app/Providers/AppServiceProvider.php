<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Overtrue\EasySms\EasySms;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 阿里短信服务器提供者注册
        $this->app->singleton(EasySms::class, function ($app) {
            return new EasySms($app['config']['easySms']);
        });
        $this->app->alias(EasySms::class, 'easysms');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // todo
        Schema::defaultStringLength(255);
    }
}
