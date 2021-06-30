<?php
use think\facade\Route;
use think\Response;

Route::miss(function() {
    return Response::create(['error'=>'未找到'], 'json', 404);
});
