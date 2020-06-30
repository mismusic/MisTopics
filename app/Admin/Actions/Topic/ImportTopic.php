<?php

namespace App\Admin\Actions\Topic;

use App\Imports\TopicsImport;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportTopic extends Action
{
    protected $selector = '.import-topic';

    public function handle(Request $request)
    {
        // $request ...
        Excel::import(new TopicsImport(), $request->file('file'));  // 导入文件里面的数据到数据表
        return $this->response()->success('导入话题列表成功')->refresh();
    }

    public function form()
    {
        $this->file('file', '请选择文件');
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default import-topic"><i class="fa fa-upload"></i>导入数据</a>
HTML;
    }
}