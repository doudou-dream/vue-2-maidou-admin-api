maidou-admin
===============
> maidou-admin是一套使用 thinkphp6、vue-element-admin  、JWT 和 RBAC鉴权的通用后台管理系统，本项目是后端项目

## 主要新特性

* `maidou-admin` 是基于 `thinkphp6` 后台快速开发框架，完全实现接口化，并有接口注释文档
* 用户登录状态基于 `lcobucci/jwt` 的状态管理
* 权限判断基于 `php-casbin/think-authz` 的 `RBAC` 授权
* topthink/think-annotation 注解路由
* 本项目为后台api服务，后台前端项目点击可查看 [`maidou-admin`](https://github.com/doudou-dream/vue-2-maidou-admin-ui) 前端项目

## 环境要求

* PHP >= 7.1.0
* thinkphp >= 6.0.11
* mysql >= 5.6
* composer >= 2.0

## 安装

1. 拉取文件

~~~
git clone https://gitee.com/doudou-y/maidou-admin-api.git
git clone git@github.com:doudou-dream/vue-2-maidou-admin-api.git
~~~

2. 安装依赖

~~~
composer install 
~~~

3. 安装sql

~~~
# 请确认当前config->database.php已经可以正常访问mysql
1. php think install
或者使用
2. 启动本地服务访问：http://localhost/install.php地址进行安装操作
~~~

4.如果需要注解路由则修改一下内容
 - 修改 \vendor\topthink\think-annotation\src\route\Rule.php文件Rule类中加入一下内容
 ```php
    /**
     * 别名
     * @var string
     */
    public $name;
```
 - 注解文档
 ```php
/**
 * 注册路由
 * @Route("login/captcha", method="GET", name="maidou.login.captcha")
 * @Route\Middleware({Auth::class, Permission::class, AllowCrossDomain::class})
 * 注解权限
 * @ApiPower\Power(title="登录模块", slug="maidou.login") 
 **/
```

![图片](/doc/1643373317475-1.jpg)

## thinkphp6模型使用事件
1. 创建
~~~
// 创建
AdminModel::create()
(new AdminModel())->saveAll()
public status function onBeforeInsert($model){}
public status function onAfterInsert($model){}
~~~
如果是使用模型创建数据方法，会触发新增前、新增后。使用模型的save()和saveAll()来新增方法也会触发这几个事件。
2. 修改
~~~
// 修改
AdminModel::where('id', $id)->find()->save()
public status function onBeforeUpdate($model){}
public status function onAfterUpdate($model){}
~~~
如果是使用模型方法查询出来数据,使用模型的save()方法来更新数据，会触发写入前、更新前、更新后、写入后事件。
3. 删除
~~~
// 删除
AdminModel::where('id', $id)->find()->delete()
public status function onBeforeDelete($model){}
public status function onAfterDelete($model){}
~~~
如果是使用模型方法查询出来数据，然后再删除数据，则会触发删除前、删除后事件。
4. 查询
~~~
// 查询
AdminModel::where('id', $id)->find()
public status function onAfterRead($model){}
~~~
如果是使用模型find()方法会触发查询后事件

## 特别鸣谢

感谢以下的项目,排名不分先后

thinkphp：http://www.thinkphp.cn

lcobucci/jwt：https://github.com/lcobucci/jwt

hg/apidoc：https://github.com/HGthecode/thinkphp-apidoc

casbin/think-authz：https://github.com/php-casbin/think-authz

## 版权信息

`maidou-admin` 遵循 `Apache2` 开源协议发布，在保留本系统版权的情况下提供商业免费使用

该系统所属版权归 maidou(https://github.com/doudou-dream) 所有
