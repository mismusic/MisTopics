<?php

use Illuminate\Database\Seeder;

class ReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(\Faker\Generator $faker)
    {
        $userIds = \App\Models\User::all(['id'])->pluck('id')->toArray();
        $topicIds = \App\Models\Topic::all(['id'])->pluck('id')->toArray();
        for ($i = 1; $i <= 40; $i ++) {
            $replies = factory(\App\Models\Reply::class, 5)->make()->each(function ($reply) use ($faker, $userIds, $topicIds) {
                $reply->user_id = $faker->randomElement($userIds);
                $reply->topic_id = $faker->randomElement($topicIds);
            })->toArray();
            $replies = array_map(function ($reply) use ($faker) {
                $reply['created_at'] = \Carbon\Carbon::createFromTimeString($reply['created_at'])->toDateTimeString();
                $reply['updated_at'] = \Carbon\Carbon::createFromTimeString($reply['updated_at'])->toDateTimeString();
                // 随机从已有的回复数据里面取出一条的id作为当前id的pid
                $pids = random_int(1, 10) > 8 ? [0] : [];
                $result = \App\Models\Reply::query()->where('topic_id', $reply['topic_id'])->inRandomOrder()->first(['id']);
                if ($result) {
                    array_push($pids, $result->id);
                }
                $pids = $pids ?: [0];
                $reply['pid'] = $faker->randomElement($pids);
                return $reply;
            }, $replies);
            // 讲回复模型写入到数据表里面
            \App\Models\Reply::query()->insert($replies);
        }
    }
}
