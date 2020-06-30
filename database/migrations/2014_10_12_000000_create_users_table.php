<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->comment('用户名，必须是唯一的');
            $table->char('phone', 11)->unique()->nullable()->index()->comment('手机号');
            $table->string('email')->unique()->nullable()->comment('邮箱号');
            $table->string('email_verified_at')->nullable()->comment('邮箱号验证时间');
            $table->string('weixin_openid')->unique()->nullable();
            $table->string('weixin_unionid')->unique()->nullable();
            $table->string('avatar')->default('')->comment('用户头像');
            $table->string('introduction')->default('')->comment('用户简介');
            $table->unsignedInteger('notification_count')->default(0)->comment('通知的个数');
            $table->string('password')->default('')->comment('用户密码');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
