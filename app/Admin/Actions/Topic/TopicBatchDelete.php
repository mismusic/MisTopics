<?php

namespace App\Admin\Actions\Topic;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class TopicBatchDelete extends BatchAction
{
    public $name = '批量删除';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            $model->forceDelete();  // 批量永久删除
        }
        return $this->response()->success('删除成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要批量永久删除吗？');
    }

}