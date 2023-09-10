<?php

// +----------------------------------------------------------------------
// | 管理接口路由定义
// +----------------------------------------------------------------------
use think\facade\Route;
use app\middleware\SystemAdminAuth;
use app\middleware\SystemAdminAuthz;

// 设置全局变量规则
Route::pattern([
    'id'    => '\w+',
    'ids'   => '[\w,]+',
    'name'  => '\w+',
    'names' => '[\w,]+',
]);
// 不注册中间件的分组
Route::group('api/admin', function () {
    // 获取字典
    Route::get('system_dict/:name', 'admin.SystemDict/read');

    // 管理员登录(请求token)
    Route::post('token', 'admin.SystemAdmin/login');
    // 管理员退出(销毁token)
    Route::delete('token', 'admin.SystemAdmin/logout');
});

// 只注册管理员认证中间件的分组
Route::group('api/admin', function () {
    // 获取字典列表
    Route::get('system_dict', 'admin.SystemDict/index');
    // 获取高级表格(ProTable)的schema描述
    Route::get('schema/protable/:name', 'admin.SystemTable/protable');
    // 获取系统表格(Formily)的schema描述
    Route::get('schema/formily/table/:name', 'admin.SystemTable/formily');
    // 获取系统表单(Formily)的schema描述
    Route::get('schema/formily/form/:name', 'admin.SystemForm/formily');

    // enum 数据源
    Route::get('enum/:controller', 'admin.:controller/enum');
    // suggest 数据源
    Route::get('suggest/:controller', 'admin.:controller/suggest');

    // 创建七牛云直传token
    Route::post('upload/token/:name', 'admin.SystemUpload/token');
    // 上传图片
    Route::post('upload/image/:name', 'admin.SystemUpload/image');
    // 上传附件
    Route::post('upload/attachment/:name', 'admin.SystemUpload/attachment');

    // 获取管理员可访问菜单
    Route::get('menus', 'admin.SystemAdmin/menus');
    // 获取管理员可访问表格
    Route::get('tables', 'admin.SystemAdmin/tables');
    // 读取管理员个人信息
    Route::get('profile', 'admin.SystemAdmin/profile');
    // 修改管理员个人信息
    Route::put('profile', 'admin.SystemAdmin/updateProfile');
})->middleware(SystemAdminAuth::class);

// 注册管理员认证和授权中间件的分组
Route::group('api/admin', function () {
    // export 导出列表
    Route::get('export/:controller', 'admin.:controller/export');
    // CRUD 获取列表
    Route::get('crud/:controller', 'admin.:controller/index');
    // CRUD 创建
    Route::post('crud/:controller', 'admin.:controller/create');
    // CRUD 获取
    Route::get('crud/:controller/:id', 'admin.:controller/read');
    // CRUD 更新
    Route::put('crud/:controller/:id', 'admin.:controller/update');
    // CRUD 删除
    Route::delete('crud/:controller/:ids', 'admin.:controller/delete');

    // REST GET操作
    Route::get('rest/:controller/:action/[:ids]', 'admin.:controller/get:action');
    // REST POST操作
    Route::post('rest/:controller/:action/[:ids]', 'admin.:controller/post:action');
    // REST PUT操作
    Route::put('rest/:controller/:action/[:ids]', 'admin.:controller/put:action');
    // REST DELETE操作
    Route::delete('rest/:controller/:action/[:ids]', 'admin.:controller/delete:action');

    // 获取配置项列表
    Route::get('system_config', 'admin.SystemConfig/index');
    // 获取配置项信息
    Route::get('system_config/:id', 'admin.SystemConfig/read');
    // 更新配置项信息
    Route::put('system_config/:id', 'admin.SystemConfig/update');
    // 创建配置项
    Route::post('system_config', 'admin.SystemConfig/create');
    // 删除配置项
    Route::delete('system_config/:ids', 'admin.SystemConfig/delete');
    // 保存系统配置
    Route::put('system_setting', 'admin.SystemConfig/setting');

    // 更新字典
    Route::put('system_dict/:name', 'admin.SystemDict/update');
    // 创建字典
    Route::post('system_dict', 'admin.SystemDict/create');
    // 删除字典
    Route::delete('system_dict/:names', 'admin.SystemDict/delete');

    // 获取管理员列表
    Route::get('system_admin', 'admin.SystemAdmin/index');
    // 更新管理员
    Route::put('system_admin/:id', 'admin.SystemAdmin/update');
    // 创建管理员
    Route::post('system_admin', 'admin.SystemAdmin/create');
    // 删除管理员
    Route::delete('system_admin/:ids', 'admin.SystemAdmin/delete');

    // 获取系统角色列表
    Route::get('system_role', 'admin.SystemRole/index');
    // 获取系统角色关联表格
    Route::get('system_role/table', 'admin.SystemRole/getTable');
    // 获取系统角色
    Route::get('system_role/:id', 'admin.SystemRole/read');
    // 更新系统角色
    Route::put('system_role/:id', 'admin.SystemRole/update');
    // 创建系统角色
    Route::post('system_role', 'admin.SystemRole/create');
    // 删除系统角色
    Route::delete('system_role/:ids', 'admin.SystemRole/delete');
    // 获取系统角色拥有权限
    Route::get('system_role/permission/:id', 'admin.SystemRole/getPermission');
    // 更新系统角色拥有权限
    Route::put('system_role/permission/:id', 'admin.SystemRole/putPermission');

    // 获取菜单列表
    Route::get('system_menu', 'admin.SystemMenu/index');
    // 获取菜单信息
    Route::get('system_menu/:id', 'admin.SystemMenu/read');
    // 更新菜单
    Route::put('system_menu/:id', 'admin.SystemMenu/update');
    // 创建菜单
    Route::post('system_menu', 'admin.SystemMenu/create');
    // 删除菜单
    Route::delete('system_menu/:ids', 'admin.SystemMenu/delete');

    // 获取表格列表
    Route::get('system_table', 'admin.SystemTable/index');
    // 获取表格信息
    Route::get('system_table/:name', 'admin.SystemTable/read');
    // 更新表格
    Route::put('system_table/:name', 'admin.SystemTable/update');
    // 创建表格
    Route::post('system_table', 'admin.SystemTable/create');
    // 删除表格
    Route::delete('system_table/:names', 'admin.SystemTable/delete');

    // 获取表单列表
    Route::get('system_form', 'admin.SystemForm/index');
    // 获取表单信息
    Route::get('system_form/:name', 'admin.SystemForm/read');
    // 更新表单
    Route::put('system_form/:name', 'admin.SystemForm/update');
    // 创建表单
    Route::post('system_form', 'admin.SystemForm/create');
    // 删除表单
    Route::delete('system_form/:names', 'admin.SystemForm/delete');
})->middleware(SystemAdminAuth::class)->middleware(SystemAdminAuthz::class);
