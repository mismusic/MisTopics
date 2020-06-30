<?php

namespace App\Listeners;

use App\Events\CreateReply;
use App\Notifications\TopicReply;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTopicReplyNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CreateReply  $event
     * @return void
     */
    public function handle(CreateReply $event)
    {
        $reply = $event->reply;
        // 通过事件触发的方式发送TopicReply通知（监听器里面的逻辑会放到队列里面执行）
        $reply->topic->user->notify(new TopicReply($reply));
    }
}
