<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index()->comment('话题标题');
            $table->text('content')->comment('话题内容');
            $table->string('description')->default('')->comment('话题描述');
            $table->softDeletes()->comment('记录软删除的时间');
            $table->unsignedBigInteger('read_count')->default(0)->comment('访问当前话题的次数');
            $table->unsignedInteger('reply_count')->default(0)->comment('当前话题的回复次数');
            $table->unsignedBigInteger('category_id')->comment('话题对应的分类id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade'); // 设置分类外键
            $table->unsignedBigInteger('user_id')->comment('话题对应的用户id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  // 设置用户外键
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
        Schema::dropIfExists('topics');
    }
}
