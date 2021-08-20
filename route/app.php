<?php

use think\facade\Route;

Route::miss(function () {

    return common_response('资源未找到', 404);
});
