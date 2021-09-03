<?php

// +----------------------------------------------------------------------
// | 管理接口路由定义
// +----------------------------------------------------------------------
use think\facade\Route;
use app\middleware\AdminAuth;

// 设置全局变量规则
Route::pattern([
    'id'   => '\d+',
    'ids'  => '[\d,]+'
]);
// 不注册中间件的分组
Route::group('admin', function () {
    // 管理员登录(请求token)
    Route::post('token', 'admin.Admin/login');
});
// 注册管理员验证中间件的分组
Route::group('admin', function () {
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
})->middleware(AdminAuth::class);
