<?php

namespace App\Admin\Actions\Resource;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResourceDelete extends RowAction
{
    public $name = '删除';

    public function handle(Model $model)
    {
        // $model ...
        Storage::disk('public')->delete($model->uri);  // 删除数据前先删除文件
        $model->delete();  // 删除模型数据
        return $this->response()->success('删除资源成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定要删除资源');
    }

}