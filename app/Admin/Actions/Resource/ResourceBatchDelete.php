<?php

namespace App\Admin\Actions\Resource;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ResourceBatchDelete extends BatchAction
{
    public $name = '批量删除';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            Storage::disk('public')->delete($model->uri);
            $model->delete();
        }

        return $this->response()->success('批量删除资源成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要批量删除资源');
    }

}