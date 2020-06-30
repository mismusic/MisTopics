<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Export\UsersExporter;
use App\Common\Utils\UploadFile;
use App\Models\Resource;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Route;

class UsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('avatar', '头像')->image('', 100, 100);
        $grid->column('username', __('用户名'));
        $grid->column('phone', __('手机号'));
        $grid->column('email', __('邮箱号'));
        $grid->column('email_verified_at', __('邮箱验证'))->display(function ($value) {
            return $value ? '已验证' : '未验证';
        });
        $grid->column('weixin_openid', __('微信 openid'));
        $grid->column('weixin_unionid', __('微信 unionid'));
        $grid->column('notification_count', __('通知数'));
        $grid->column('created_at', __('创建时间'))->sortable();
        $grid->column('updated_at', __('更新时间'));

        // 过滤数据
        $grid->filter(function (Grid\Filter $filter) {
            $filter->like('username', '用户名');
        });

        // 禁用创建按钮
        $grid->disableCreateButton();

        // 去掉批量操作
        $grid->disableBatchActions();

        // 导出数据
        $grid->exporter(new UsersExporter());

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('username', __('用户名'));
        $show->field('phone', __('Phone'));
        $show->field('email', __('Email'));
        $show->field('email_verified_at', __('Email verified at'));
        $show->field('weixin_openid', __('Weixin openid'));
        $show->field('weixin_unionid', __('Weixin unionid'));
        $show->field('avatar', __('Avatar'))->image();
        $show->field('introduction', __('Introduction'));
        $show->field('notification_count', __('Notification count'));
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
        $form = new Form(new User());
        $routeName = Route::currentRouteName();  // 获取当前路由名
        $usernameUnique = '';

        switch ($routeName) {
            case admin_route_prefix('users.store'):
                $usernameUnique = '|unique';
                break;
            case admin_route_prefix('users.update'):
                $usernameUnique = '|unique:users,username,' . request()->route('user');
                break;
        }

        $form->text('username', __('用户名'))->rules('required|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-_]+$/u|between:1,25' . $usernameUnique);
        $form->image('avatar', __('头像'))->rules('file|mimes:jpg,jpeg,png|dimensions:min_width=100,min_height=100')
            ->downloadable();
        $form->text('introduction', __('介绍'))->rules('required|between:1,255');
        $form->password('password', __('密码'))->rules('nullable|alpha_dash|between:6,15');

        return $form;
    }

    public function update($id)
    {
        $password = request()->input('password');
        $resource = new Resource();
        $user = User::query()->where('id', $id)->firstOrFail();
        // 获取表单数据
        $data = \request()->all();
        // 表单验证
        if ($validationMessages = $this->form()->validationMessages($data)) {  // 验证失败就返回错误信息
            return back()->withInput()->withErrors($validationMessages);
        }
        // 检查有没有上传头像
        if (request()->hasFile('avatar')) {
            $fileInfo = UploadFile::localStorage(request()->file('avatar'), 'images', $user->id, 'image', 250);  // 进行头像的上传
            // 保存头像信息到资源模型
            $resource->fill([
                'type' => $fileInfo['type'],
                'name' => $fileInfo['fileName'],
                'original_name' => $fileInfo['fileOriginalName'],
                'uri' => $fileInfo['fileUri'],
            ]);  // 批量填充数据
            $resource->user()->associate($user->id);
            $resource->save();  // 保存到数据表
            // 删除以前的头像文件
            $user->deleteAvatar();
        }
        // 进行用户数据更新
        $update = [
            'username' => request()->input('username'),
            'introduction' => request()->input('introduction'),
        ];
        if ($password) {
            $update['password'] = bcrypt($password);  // 如果密码存在，就进行加密
        }
        if ($resource->id) {
            $update['avatar'] = $resource->id;
        }
        $user->update($update);  // 进行用户信息的更新
        admin_toastr('修改用户信息成功');
        return redirect()->route(admin_route_prefix('users.index'));
    }


}
