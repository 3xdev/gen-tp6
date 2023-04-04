<?php

// +----------------------------------------------------------------------
// | 管理接口路由定义
// +----------------------------------------------------------------------
use think\facade\Route;
use app\middleware\ShopUserAuth;

// 设置全局变量规则
Route::pattern([
    'id'    => '\w+',
    'ids'   => '[\w,]+',
    'name'  => '\w+',
    'names' => '[\w,]+',
]);
// 不注册中间件的分组
Route::group('api/open', function () {
    // 登录(请求token)
    Route::post('token', 'open.User/login');
});

// 注册用户认证中间件的分组
Route::group('api/open', function () {
    // 用户退出(销毁token)
    Route::delete('token', 'open.User/logout');

    // 创建云存储直传token
    Route::post('upload/token/:name', 'open.Upload/token');

    // REST GET操作
    Route::get('rest/:controller/:action/[:ids]', 'open.:controller/get:action');
    // REST POST操作
    Route::post('rest/:controller/:action/[:ids]', 'open.:controller/post:action');
    // REST PUT操作
    Route::put('rest/:controller/:action/[:ids]', 'open.:controller/put:action');
    // REST DELETE操作
    Route::delete('rest/:controller/:action/[:ids]', 'open.:controller/delete:action');
})->middleware(UserAuth::class);
