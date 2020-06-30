<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Reply\ReplyBatchDelete;
use App\Admin\Actions\Reply\ReplyBatchRestore;
use App\Admin\Actions\Reply\ReplyBatchSoftDelete;
use App\Admin\Actions\Reply\ReplyDelete;
use App\Admin\Actions\Reply\ReplyRestore;
use App\Admin\Actions\Reply\ReplySoftDelete;
use App\Admin\Extensions\Export\RepliesExporter;
use App\Models\Reply;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RepliesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '回复';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Reply());

        $grid->column('id', __('Id'))->sortable();
        $grid->column('pid', __('Pid'));
        $grid->column('content', __('Content'))->editable('textarea');
        $grid->column('user.username', __('User'));
        $grid->column('topic_id', __('Topic id'));
        $grid->column('created_at', __('Created at'))->sortable();
        $grid->column('updated_at', __('Updated at'));

        // 过滤数据
        $grid->filter(function (Grid\Filter $filter) {
            $filter->scope('trashed', '回收站')->onlyTrashed();
            // 搜索过滤
            $filter->like('content', '内容');
        });

        // 禁用创建按钮
        $grid->disableCreateButton();

        // 行内操作
        $grid->actions(function (Grid\Displayers\DropdownActions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();

            if (request('_scope_') === 'trashed') {
                $actions->add(new ReplyRestore());
                $actions->add(new ReplyDelete());
            } else {
                $actions->add(new ReplySoftDelete());  // 回收数据
            }
        });

        // 批量操作
        $grid->batchActions(function (Grid\Tools\BatchActions $batchActions) {
            $batchActions->disableDelete();
            if (request('_scope_') === 'trashed') {
                $batchActions->add(new ReplyBatchRestore());
                $batchActions->add(new ReplyBatchDelete());
            } else {
                $batchActions->add(new ReplyBatchSoftDelete());
            }
        });

        // 导出数据
        $grid->exporter(new RepliesExporter());

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
        $show = new Show(Reply::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('pid', __('Pid'));
        $show->field('content', __('Content'));
        $show->field('user_id', __('User id'));
        $show->field('topic_id', __('Topic id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        // 面板工具设置
        $show->panel()->tools(function (Show\Tools $tools) {
            $tools->disableDelete();
            $tools->disableEdit();
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Reply());

        $form->number('pid', __('Pid'));
        $form->textarea('content', __('Content'));
        $form->number('user_id', __('User id'));
        $form->number('topic_id', __('Topic id'));

        return $form;
    }

}
