<?php

namespace App\Notifications;

use App\Models\Reply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopicReply extends Notification
{
    use Queueable;

    public $reply;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reply $reply)
    {
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $notifiable->email ? ['mail', 'database'] : ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = $this->reply->user->username . '回复了你的主题《' . htmlspecialchars($this->reply->topic->title) . '》';
        return (new MailMessage)
                    ->subject(config('app.name') . '主题回复通知')
                    ->greeting($notifiable->username . '，你好')
                    ->line($message)
                    ->action('点击查看详情', route(get_api_prefix() . 'topics.show', $this->reply->topic->id))
                    ->line('谢谢你的支持！')
                    ->salutation(config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'topic_id' => $this->reply->topic->id,
            'title' => $this->reply->topic->title,
            'reply_id' => $this->reply->id,
            'reply_content' => $this->reply->content,
            'user_id' => $this->reply->user->id,
            'user_name' => $this->reply->user->username,
            'user_avatar' => $this->reply->user->avatar,
        ];
    }
}
