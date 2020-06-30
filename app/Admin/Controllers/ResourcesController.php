<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Resource\ResourceBatchDelete;
use App\Admin\Actions\Resource\ResourceDelete;
use App\Admin\Extensions\Export\ResourcesExporter;
use App\Models\Resource;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ResourcesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '资源';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Resource());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('user.username', __('用户名'));
        $grid->column('type', __('资源类型'))->editable();
        $grid->column('name', __('文件名'))->editable();
        $grid->column('original_name', __('文件源名称'))->editable();
        $grid->column('uri', __('资源地址'));
        $states = [
            'on'  => ['value' => 1, 'text' => '公开', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '私有', 'color' => 'danger'],
        ];
        $grid->column('public', __('资源共享'))->switch($states);
        $grid->column('created_at', __('Created at'))->sortable();
        $grid->column('updated_at', __('Updated at'));

        // 禁用创建按钮
        $grid->disableCreateButton();

        // 行操作
        $grid->actions(function (Grid\Displayers\DropdownActions $actions) {
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
            $actions->add(new ResourceDelete());
        });

        // 批量操作
        $grid->batchActions(function (Grid\Tools\BatchActions $batchActions) {
            $batchActions->disableDelete();
            $batchActions->add(new ResourceBatchDelete());
        });

        // 过滤数据
        $grid->filter(function (Grid\Filter $filter) {
            $filter->like('original_name', '文件源名称');
        });

        // 导出数据
        $grid->exporter(new ResourcesExporter());

        return $grid;
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Resource());

        $form->number('user_id', __('User id'));
        $form->text('type', __('Type'));
        $form->text('name', __('Name'));
        $form->text('original_name', __('Original name'));
        $form->text('uri', __('Uri'));
        $form->text('description', __('Description'));
        $form->switch('public', __('Public'))->default(1);

        return $form;
    }
}
