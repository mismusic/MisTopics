<?php

namespace App\Admin\Actions\Reply;

use App\Common\Utils\Utils;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class ReplyBatchSoftDelete extends BatchAction
{
    public $name = '批量回收';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            $model->delete();
        }

        return $this->response()->success('批量回收数据成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要批量回收数据');
    }

}