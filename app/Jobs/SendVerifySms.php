<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendVerifySms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $easySms;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($easySms)
    {
        // todo
        $this->easySms = $easySms;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // todo
        // 通过队列的方式发送注册短信
        try {
            $this->easySms['easySms']->send($this->easySms['phone'], [
                'template' => config('easySms.gateways.aliyun.template_code'),
                'data' => [
                    'code' => $this->easySms['code'],
                ]
            ]);
        } catch (NoGatewayAvailableException $e) {
            $message = $e->getException('aliyun')->getMessage();
            // 打印发送短信失败的错误信息到日志文件
            Log::info($message);
        }
    }
}
