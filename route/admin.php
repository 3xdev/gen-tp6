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
    'name'  => '\w+'
]);
// 不注册中间件的分组
Route::group('api/admin', function () {
    // 获取字典
    Route::get('dicts/:name', 'admin.Dict/read');

    // 管理员登录(请求token)
    Route::post('token', 'admin.Admin/login');
});
// 注册管理员验证中间件的分组
Route::group('api/admin', function () {
    // 获取Formily的schema描述
    Route::get('schemas/:name', 'admin.Schema/read');

    // 上传图片
    Route::post('upload/image/:name', 'admin.Upload/image');
    // 上传附件
    Route::post('upload/attachment/:name', 'admin.Upload/attachment');
    // 管理员退出(销毁token)
    Route::delete('token', 'admin.Admin/logout');
    // 读取管理员个人信息
    Route::get('profile', 'admin.Admin/profile');
    // 修改管理员个人信息
    Route::put('profile', 'admin.Admin/updateProfile');
    // 获取管理员列表
    Route::get('admins', 'admin.Admin/index');
    // 更新管理员信息
    Route::put('admins/:id', 'admin.Admin/update');
    // 创建管理员
    Route::post('admins', 'admin.Admin/create');
    // 删除管理员
    Route::delete('admins/:ids', 'admin.Admin/delete');

    // 获取字典列表
    Route::get('dicts', 'admin.Dict/index');
    // 更新字典
    Route::put('dicts/:name', 'admin.Dict/update');
    // 创建字典
    Route::post('dicts', 'admin.Dict/create');
    // 删除字典
    Route::delete('dicts/:name', 'admin.Dict/delete');

    // 保存系统配置
    Route::put('setting', 'admin.Config/setting');
    // 获取配置项列表
    Route::get('configs', 'admin.Config/index');
    // 获取配置项信息
    Route::get('configs/:id', 'admin.Config/read');
    // 更新配置项信息
    Route::put('configs/:id', 'admin.Config/update');
    // 创建配置项
    Route::post('configs', 'admin.Config/create');
    // 删除配置项
    Route::delete('configs/:ids', 'admin.Config/delete');
})->middleware(AdminAuth::class);
