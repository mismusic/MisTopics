<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SeederCategoriesData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $dateTime = now()->toDateTimeString();
            $data = [
                [
                    'pid' => 0,
                    'name' => '默认版块',
                    'description' => '默认版块',
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ],
                [
                    'pid' => 0,
                    'name' => '美女图片',
                    'description' => '分享美女图片，和生活图片',
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ],
                [
                    'pid' => 0,
                    'name' => '技术分享',
                    'description' => '交流分享技术',
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ],
            ];
            DB::table('categories')->insert($data);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            DB::table('categories')->truncate();  // 请求表里面的所有数据
        });
    }
}
