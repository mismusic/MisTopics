<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use App\Models\Topic;

class TopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        // todo
        $userIds = [1, 2, 3];
        $categoryIds = \App\Models\Category::query()->inRandomOrder()->get(['id'])->pluck('id');
        $topics = factory(Topic::class, 20)->make()->each(function ($topic) use ($faker, $userIds, $categoryIds) {
            $topic->user_id = $faker->randomElement($userIds);
            $topic->category_id = $faker->randomElement($categoryIds);
        })->toArray();
        Topic::query()->insert($topics);  // 保存多个主题模型数据到数据表
    }
}
