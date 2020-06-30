<?php

namespace App\Admin\Actions\Topic;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class TopicBatchRestore extends BatchAction
{
    public $name = '批量恢复';

    public function handle(Collection $collection)
    {
        $collection->each->restore();  // 批量恢复软删除的数据
        return $this->response()->success('批量恢复成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要批量恢复吗？');
    }

}