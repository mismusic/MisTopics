<?php

namespace App\Observers;

use App\Jobs\GenerateSlug;
use App\Models\Topic;

class TopicObserver
{

    public function saving(Topic $topic)
    {
        $topic->content = clean($topic->content, 'topic_content');
        $topic->description = topic_description($topic->content);
    }

    /**
     * Handle the topic "created" event.
     *
     * @param  \App\Model\Topic  $topic
     * @return void
     */
    public function saved(Topic $topic)
    {

    }

    /**
     * Handle the topic "updated" event.
     *
     * @param  \App\Model\Topic  $topic
     * @return void
     */
    public function updated(Topic $topic)
    {
        //
    }

    /**
     * Handle the topic "deleted" event.
     *
     * @param  \App\Model\Topic  $topic
     * @return void
     */
    public function deleted(Topic $topic)
    {
        //
    }

    /**
     * Handle the topic "restored" event.
     *
     * @param  \App\Model\Topic  $topic
     * @return void
     */
    public function restored(Topic $topic)
    {
        //
    }

    /**
     * Handle the topic "force deleted" event.
     *
     * @param  \App\Model\Topic  $topic
     * @return void
     */
    public function forceDeleted(Topic $topic)
    {
        //
    }
}
