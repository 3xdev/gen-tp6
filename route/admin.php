<?php

// +----------------------------------------------------------------------
// | 管理接口路由定义
// +----------------------------------------------------------------------
use think\facade\Route;
use app\middleware\AdminAuth;

// 设置全局变量规则
Route::pattern([
    'id'    => '\d+',
    'ids'   => '[\d,]+',
    'name'  => '\w+',
    'names' => '[\w,]+'
]);
// 不注册中间件的分组
Route::group('api/admin', function () {
    // 获取字典
    Route::get('dict/:name', 'admin.Dict/read');

    // 管理员登录(请求token)
    Route::post('token', 'admin.Admin/login');
});
// 注册管理员验证中间件的分组
Route::group('api/admin', function () {
    // 获取高级表格(ProTable)的schema描述
    Route::get('schema/protable/:name', 'admin.Table/schema');
    // 获取表单(Formily)的schema描述
    Route::get('schema/formily/:name', 'admin.Table/formily');

    // 上传图片
    Route::post('upload/image/:name', 'admin.Upload/image');
    // 上传附件
    Route::post('upload/attachment/:name', 'admin.Upload/attachment');

    // 获取配置项列表
    Route::get('config', 'admin.Config/index');
    // 获取配置项信息
    Route::get('config/:id', 'admin.Config/read');
    // 更新配置项信息
    Route::put('config/:id', 'admin.Config/update');
    // 创建配置项
    Route::post('config', 'admin.Config/create');
    // 删除配置项
    Route::delete('config/:ids', 'admin.Config/delete');

    // 获取字典列表
    Route::get('dict', 'admin.Dict/index');
    // 更新字典
    Route::put('dict/:name', 'admin.Dict/update');
    // 创建字典
    Route::post('dict', 'admin.Dict/create');
    // 删除字典
    Route::delete('dict/:names', 'admin.Dict/delete');

    // 保存系统配置
    Route::put('setting', 'admin.Config/setting');

    // 管理员退出(销毁token)
    Route::delete('token', 'admin.Admin/logout');
    // 读取管理员个人信息
    Route::get('profile', 'admin.Admin/profile');
    // 修改管理员个人信息
    Route::put('profile', 'admin.Admin/updateProfile');
    // 获取管理员列表
    Route::get('admin', 'admin.Admin/index');
    // 更新管理员
    Route::put('admin/:id', 'admin.Admin/update');
    // 创建管理员
    Route::post('admin', 'admin.Admin/create');
    // 删除管理员
    Route::delete('admin/:ids', 'admin.Admin/delete');

    // 获取菜单列表
    Route::get('menu', 'admin.Menu/index');
    // 获取菜单信息
    Route::get('menu/:id', 'admin.Menu/read');
    // 更新菜单
    Route::put('menu/:id', 'admin.Menu/update');
    // 创建菜单
    Route::post('menu', 'admin.Menu/create');
    // 删除菜单
    Route::delete('menu/:ids', 'admin.Menu/delete');

    // 获取表格列表
    Route::get('table', 'admin.Table/index');
    // 获取表格信息
    Route::get('table/:name', 'admin.Table/read');
    // 更新表格
    Route::put('table/:name', 'admin.Table/update');
    // 创建表格
    Route::post('table', 'admin.Table/create');
    // 删除表格
    Route::delete('table/:names', 'admin.Table/delete');
})->middleware(AdminAuth::class);
