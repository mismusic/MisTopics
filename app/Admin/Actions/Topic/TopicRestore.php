<?php

namespace App\Admin\Actions\Topic;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class TopicRestore extends RowAction
{
    public $name = '恢复';

    public function handle(Model $model)
    {
        $model->restore();  // 获取模型软删除的数据
        return $this->response()->success('恢复成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要恢复吗？');
    }

}