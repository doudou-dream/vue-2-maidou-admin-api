<?php

namespace app\admin\controller;

use app\common\support\Files;
use app\admin\middleware\Auth;
use app\admin\middleware\Permission;
use think\annotation\route\Route;
use think\annotation\route\Middleware;
use think\middleware\AllowCrossDomain;
use app\common\support\annotation as ApiPower;
use hg\apidoc\annotation as Apidoc;
/**
 * 注解权限
 * @ApiPower\Power(title="公共模块", slug="maidou.common")
 * 注解文档
 * @Apidoc\Title("上传")
 */
//注册路由中间件
#[Middleware(Auth::class, Permission::class, AllowCrossDomain::class)]
class Common
{
    #[Route('POST', '/upload', ['slug' => 'maidou.common.upload'])]
    public function upload()
    {
        return (new Files())->upload('file');
    }
}