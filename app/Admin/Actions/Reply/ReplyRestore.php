<?php

namespace App\Admin\Actions\Reply;

use App\Common\Utils\Utils;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ReplyRestore extends RowAction
{
    public $name = '恢复';

    public function handle(Model $model)
    {
        // $model ...
        $subIds = Utils::getSubTreeIds($model->onlyTrashed()->get(['id', 'pid']), $model->id);
        $model->whereIn('id', $subIds)->restore();
        return $this->response()->success('恢复数据成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要恢复数据');
    }

}