<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class JwtTokenGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt-token:generate
                            {--ttl=0 : 设置生成token的生效时间}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成一个JWT Token';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 弹出一个命令行输入框，可以输入对应的用户id
        $id = $this->ask('请求输入用户id: ');
        // 根据用户id获取用户模型
        $user = User::query()->where('id', $id)->first();
        if (! $user) {
            return $this->error('该用户不存在');
        }
        // 如果选择值为null，就把ttl设置为一年的时间
        $ttl = $this->option('ttl');
        if (empty($ttl)) {
            $ttl = 365 * 24 * 60;
        }
        // 获取用户对应的token值
        $token = auth()->setTTL($ttl)->useResponsable(false)->login($user)->get();
        // 返回 token 值
        return $this->info($token);
    }
}
