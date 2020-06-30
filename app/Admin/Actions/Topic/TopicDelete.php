<?php

namespace App\Admin\Actions\Topic;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class TopicDelete extends RowAction
{
    public $name = '删除';

    public function handle(Model $model)
    {
        $model->forceDelete();  // 永久删除回收站里面的数据
        return $this->response()->success('删除成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要永久删除吗？');
    }

}