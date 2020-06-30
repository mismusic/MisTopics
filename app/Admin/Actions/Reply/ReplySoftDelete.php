<?php

namespace App\Admin\Actions\Reply;

use App\Common\Utils\Utils;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ReplySoftDelete extends RowAction
{
    public $name = '回收';

    public function handle(Model $model)
    {
        // $model ...
        $subIds = Utils::getSubTreeIds($model->get(['id', 'pid']), $model->id);  // 查询出当前回复下面的所有子回复，包括自己
        $model->whereIn('id', $subIds)->delete();  // 删除该回复，以前该回复下面的所有子分回复
        return $this->response()->success('回收数据成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要回收数据');
    }

}