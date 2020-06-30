<?php

namespace App\Admin\Actions\Reply;

use App\Common\Utils\Utils;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ReplyDelete extends RowAction
{
    public $name = '删除';

    public function handle(Model $model)
    {
        // $model ...
        $subIds = Utils::getSubTreeIds($model->onlyTrashed()->get(['id', 'pid']), $model->id);
        $model->whereIn('id', $subIds)->forceDelete();
        return $this->response()->success('删除数据成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要删除数据');
    }
}