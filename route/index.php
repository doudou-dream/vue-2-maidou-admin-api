<?php

use think\facade\Route;

// 写个自动加载多应用路由
require __DIR__."/../app/admin/route/route.php";
Route::miss(function () {
    return json([
        'code'    => \app\common\http\ResponseCode::INVALID,
        'message' => 'error',
    ]);
});
