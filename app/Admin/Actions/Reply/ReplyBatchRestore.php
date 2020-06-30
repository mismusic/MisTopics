<?php

namespace App\Admin\Actions\Reply;

use App\Common\Utils\Utils;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class ReplyBatchRestore extends BatchAction
{
    public $name = '批量恢复';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            $model->restore();
        }

        return $this->response()->success('批量回复成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要批量恢复');
    }

}