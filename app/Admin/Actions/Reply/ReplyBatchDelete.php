<?php

namespace App\Admin\Actions\Reply;

use App\Common\Utils\Utils;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class ReplyBatchDelete extends BatchAction
{
    public $name = '批量删除';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            $model->forceDelete();
        }

        return $this->response()->success('批量删除数据成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要批量删除数据');
    }

}