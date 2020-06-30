<?php

namespace App\Observers;

use App\Models\Reply;
use Illuminate\Support\Facades\DB;

class ReplyObserver
{

    public function creating(Reply $reply)
    {
        $replyCount = $reply->topic->replies->count();  // 获取当前主题下面的回复数
        DB::table('topics')->where('id', $reply->topic_id)->update([
            'reply_count' => $replyCount,
        ]);  // 更新主题表里面的reply_count字段
    }

    /**
     * Handle the reply "created" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function created(Reply $reply)
    {
        //
    }

    /**
     * Handle the reply "updated" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function updated(Reply $reply)
    {
        //
    }

    /**
     * Handle the reply "deleted" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function deleted(Reply $reply)
    {
        //
    }

    /**
     * Handle the reply "restored" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function restored(Reply $reply)
    {
        //
    }

    /**
     * Handle the reply "force deleted" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function forceDeleted(Reply $reply)
    {
        //
    }
}
