<?php

namespace App\Jobs;

use App\Mail\EmailVerify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailVerify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 在队列任务里面进行邮件的发送
        try {
            Mail::send(new EmailVerify($this->data));
        } catch (\Exception $e) {
            // 打印出发送邮件的错误原因
            Log::warning('EmailVerify Error: ' . $e->getMessage());
        }
    }
}
