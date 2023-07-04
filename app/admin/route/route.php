<?php

use app\admin\middleware;
use think\facade\Route;

// 登录功能
$res = Route::group('/pc/v1',function () {
    $common = '\\app\\admin\\controller\\system\\';
    $commonPath = '\\app\\admin\\controller\\';
    // 登录
    Route::get('/login/captcha', $common.'Login@captcha')->name('maidou.login.captcha');// 验证码
    Route::post('/login', $common.'Login@login')->name('maidou.login.login');// 登录
    Route::delete('/login/log-out', $common.'Login@logOut')->name('maidou.login.loginOut');// 退出登录
    Route::get('/login/info', $common.'Login@info')->name('maidou.login.info');// 登录信息
    // 账号管理
    Route::get('/admin', $common.'Admin@index')->name('maidou.admin.index');// 列表
    Route::post('/admin', $common.'Admin@create')->name('maidou.admin.create');// 创建
    Route::patch('/admin/modify-password', $common.'Admin@modifyPassword')->name('maidou.admin.modifyPassword');// 密码修改
    Route::get('/admin/:id', $common.'Admin@detail')->name('maidou.admin.detail')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 详情
    Route::put('/admin/:id', $common.'Admin@update')->name('maidou.admin.update')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 修改
    Route::delete('/admin/:id', $common.'Admin@delete')->name('maidou.admin.delete')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 删除
    Route::patch('/admin/:id', $common.'Admin@access')->name('maidou.admin.access')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 授权
    Route::get('/admin/group', $common.'Admin@group')->name('maidou.admin.group');// 角色列表
    // 角色管理
    Route::get('/group', $common.'AuthGroup@index')->name('maidou.group.index');// 列表
    Route::post('/group', $common.'AuthGroup@create')->name('maidou.group.create');// 创建
    Route::get('/group/:id', $common.'AuthGroup@detail')->name('maidou.group.detail')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 详情
    Route::put('/group/:id', $common.'AuthGroup@update')->name('maidou.group.update')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 修改
    Route::delete('/group/:id', $common.'AuthGroup@delete')->name('maidou.group.delete')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 删除
    Route::patch('/group/:id', $common.'AuthGroup@access')->name('maidou.group.access')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 授权
    Route::get('/group/rule', $common.'AuthGroup@rule')->name('maidou.group.rule')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 列表
    // 权限管理
    Route::get('/rule', $common.'AuthRule@index')->name('maidou.rule.index');// 列表
    Route::post('/rule', $common.'AuthRule@create')->name('maidou.rule.create');// 创建
    Route::get('/rule/:id', $common.'AuthRule@detail')->name('maidou.rule.detail')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 详情
    Route::put('/rule/:id', $common.'AuthRule@update')->name('maidou.rule.update')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 修改
    Route::delete('/rule/:id', $common.'AuthRule@delete')->name('maidou.rule.delete')->pattern(['id'=>'[A-Za-z0-9]{32}']);// 删除
    // 文件上传
    Route::post('/upload', $commonPath.'Common@upload')->name('maidou.rule.upload');// 上传文件
})
    ->middleware(middleware\Auth::class)// jwt认证
    ->middleware(middleware\Permission::class)// 权限认证
    ->allowCrossDomain();// 跨域
