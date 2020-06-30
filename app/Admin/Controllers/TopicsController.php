<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Topic\ImportTopic;
use App\Admin\Actions\Topic\TopicBatchDelete;
use App\Admin\Actions\Topic\TopicBatchRestore;
use App\Admin\Actions\Topic\TopicDelete;
use App\Admin\Actions\Topic\TopicRestore;
use App\Admin\Extensions\Export\TopicsExporter;
use App\Common\ApiReturnCode;
use App\Common\Utils\UploadFile;
use App\Common\Utils\Utils;
use App\Models\Category;
use App\Models\Topic;
use App\Models\User;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\Storage;

class TopicsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '话题';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Topic());

        $grid->column('id', __('ID'));
        $grid->column('title', __('标题'))
            ->modal('最新回复', function ($model) {
                $replies = $model->replies()->with('user')->take(15)->get()->map(function ($reply) {
                    $data = $reply->only(['id', 'content', 'created_at']);
                    array_splice($data, 2, 0, $reply->user->username);
                    return $data;
                });
                return new Table(['ID', '回复内容', '用户名', '创建时间'], $replies->toArray());
            })->style('max-width:280px');
        $grid->column('content', __('内容'))->style('max-width:400px;word-break:break-all;');
        $grid->column('description', __('描述'))->style('max-width:280px');
        $grid->column('read_count', __('阅读数'));
        $grid->column('reply_count', __('回复数'));
        $grid->column('category.name', __('分类名'));
        $grid->column('user.username', __('用户名'));
        $grid->column('created_at', __('创建时间'));
        $grid->column('updated_at', __('更新时间'));

        // 数据过滤
        $grid->filter(function (Grid\Filter $filter) {
            $filter->scope('trashed', '回收站')->onlyTrashed();
        });

        // 每行方法
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if (request('_scope_') === 'trashed') {
                // 禁用每行方法里面的所有操作
                $actions->disableView();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->add(new TopicRestore());  // 添加当前行的恢复
                $actions->add(new TopicDelete());  // 添加当前行的永久删除
            }
        });

        // 工具栏方法
        $grid->batchActions(function (Grid\Tools\BatchActions $batchActions) {
            if (request()->query('_scope_') === 'trashed')
            {
                $batchActions->disableDelete();  // 禁用默认的批量删除
                $batchActions->add(new TopicBatchRestore());  // 添加批量恢复
                $batchActions->add(new TopicBatchDelete());  // 添加批量删除
            }
        });

        // 工具栏导入数据
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new ImportTopic());  // 在工具栏列表的追加一个导入数据的按钮
        });

        // 导出数据
        $grid->exporter(new TopicsExporter());

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Topic::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'))->unescape();
        $show->field('description', __('Description'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('read_count', __('Read count'));
        $show->field('reply_count', __('Reply count'));
        $show->field('category.name', __('Category id'));
        $show->field('user.username', __('User id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        Admin::style('.simditor .simditor-popover {z-index: 10}');  // 添加一个样式

        $form = new Form(new Topic());

        // 获取要选择的数据
        $categories = collect(Utils::getTreeDataIteration(Category::all()->toArray()))->pluck('name', 'id');  // 分类列表
        $users = User::all()->pluck('username', 'id');

        $form->text('title', __('Title'))->rules(['required', 'between:1,255']);
        $form->simditor('content', __('Content'))->rules(['required', 'min:1']);
        $form->select('category_id', __('Category id'))->options($categories)->rules(['required', 'exists:categories,id']);
        $form->select('user_id', __('User'))->options($users)->rules(['required', 'exists:users,id']);

        return $form;
    }


    public function uploadFile()
    {
        // 检查用户权限，如果没有upload-file权限，就禁止上传文件
        if (! Permission::check('upload-file')) {
            return [
                'success' => false,
                'msg' => ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_UPLOAD_FILE_PERMISSION_DENIED),
                'file_path' => '',
            ];
        }
        // 检查上传的文件是否为空，如果为空就返回错误
        if (! request()->hasFile('file')) {
            return [
                'success' => false,
                'msg' => ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_UPLOAD_FILE_NOT_NULL),
                'file_path' => '',
            ];
        }
        // 进行文件的上传
        $fileInfo = UploadFile::localStorage(request()->file('file'),
            config('admin.upload.directory.file'),
            Admin::user()->id, 'file');

        // 返回文件信息
        return [
            'success' => true,
            'msg' => ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_UPLOAD_FILE_SUCCESS),
            'file_path' => Storage::disk('public')->url($fileInfo['fileUri']),
        ];
    }

}
