<?php

// +----------------------------------------------------------------------
// | 管理接口路由定义
// +----------------------------------------------------------------------
use think\facade\Route;
use app\middleware\SystemAdminAuth;

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
    Route::get('dict/:name', 'admin.SystemDict/read');

    // 管理员登录(请求token)
    Route::post('token', 'admin.SystemAdmin/login');
});
// 注册管理员验证中间件的分组
Route::group('api/admin', function () {
    // 获取高级表格(ProTable)的schema描述
    Route::get('schema/protable/:name', 'admin.SystemTable/protable');
    // 获取表单(Formily)的schema描述
    Route::get('schema/formily/:name', 'admin.SystemTable/formily');

    // suggest 数据源
    Route::get('suggest/:controller', 'admin.:controller/suggest');
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
    Route::get('rest/:controller/:action/:ids', 'admin.:controller/get:action');
    // REST POST操作
    Route::post('rest/:controller/:action/:ids', 'admin.:controller/post:action');
    // REST PUT操作
    Route::put('rest/:controller/:action/:ids', 'admin.:controller/put:action');
    // REST DELETE操作
    Route::delete('rest/:controller/:action/:ids', 'admin.:controller/delete:action');

    // 创建七牛云直传token
    Route::post('upload/token/:name', 'admin.SystemUpload/token');
    // 上传图片
    Route::post('upload/image/:name', 'admin.SystemUpload/image');
    // 上传附件
    Route::post('upload/attachment/:name', 'admin.SystemUpload/attachment');

    // 获取配置项列表
    Route::get('config', 'admin.SystemConfig/index');
    // 获取配置项信息
    Route::get('config/:id', 'admin.SystemConfig/read');
    // 更新配置项信息
    Route::put('config/:id', 'admin.SystemConfig/update');
    // 创建配置项
    Route::post('config', 'admin.SystemConfig/create');
    // 删除配置项
    Route::delete('config/:ids', 'admin.SystemConfig/delete');

    // 获取字典列表
    Route::get('dict', 'admin.SystemDict/index');
    // 更新字典
    Route::put('dict/:name', 'admin.SystemDict/update');
    // 创建字典
    Route::post('dict', 'admin.SystemDict/create');
    // 删除字典
    Route::delete('dict/:names', 'admin.SystemDict/delete');

    // 保存系统配置
    Route::put('setting', 'admin.SystemConfig/setting');

    // 管理员退出(销毁token)
    Route::delete('token', 'admin.SystemAdmin/logout');
    // 读取管理员个人信息
    Route::get('profile', 'admin.SystemAdmin/profile');
    // 修改管理员个人信息
    Route::put('profile', 'admin.SystemAdmin/updateProfile');
    // 获取管理员列表
    Route::get('admin', 'admin.SystemAdmin/index');
    // 更新管理员
    Route::put('admin/:id', 'admin.SystemAdmin/update');
    // 创建管理员
    Route::post('admin', 'admin.SystemAdmin/create');
    // 删除管理员
    Route::delete('admin/:ids', 'admin.SystemAdmin/delete');

    // 获取菜单列表
    Route::get('menu', 'admin.SystemMenu/index');
    // 获取菜单信息
    Route::get('menu/:id', 'admin.SystemMenu/read');
    // 更新菜单
    Route::put('menu/:id', 'admin.SystemMenu/update');
    // 创建菜单
    Route::post('menu', 'admin.SystemMenu/create');
    // 删除菜单
    Route::delete('menu/:ids', 'admin.SystemMenu/delete');

    // 获取表格列表
    Route::get('table', 'admin.SystemTable/index');
    // 获取表格信息
    Route::get('table/:name', 'admin.SystemTable/read');
    // 更新表格
    Route::put('table/:name', 'admin.SystemTable/update');
    // 创建表格
    Route::post('table', 'admin.SystemTable/create');
    // 删除表格
    Route::delete('table/:names', 'admin.SystemTable/delete');
})->middleware(SystemAdminAuth::class);
