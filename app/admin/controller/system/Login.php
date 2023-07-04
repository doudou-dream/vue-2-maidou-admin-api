<?php
declare (strict_types=1);

namespace app\admin\controller\system;

use app\admin\model\Admin as AdminModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\admin\model\AuthRule as AuthRuleModel;
use app\common\BaseController;
use app\common\http\ResponseCode;
use app\common\support\annotation as ApiPower;
use app\common\support\Captcha;
use app\common\support\Token;
use Exception;
use hg\apidoc\annotation as Apidoc;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\helper\Arr;
use think\middleware\AllowCrossDomain;
use think\Request;
use app\admin\middleware\Auth;
use app\admin\middleware\Permission;
use think\annotation\route\Middleware;
use think\annotation\route\Route;

// 权限注解
/**
 * 注解权限
 * @ApiPower\Power(title="登录模块", slug="maidou.login")
 * 注解文档
 * @Apidoc\Title("登录")
 */
//注册路由中间件
#[Middleware(Auth::class, Permission::class, AllowCrossDomain::class)]
class Login extends BaseController
{
    /**
     * 注解权限
     * @ApiPower\Power(title="验证码", url="/login/captcha", method="GET", slug="maidou.login.captcha")
     * 注解文档
     * @Apidoc\Title("验证码")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/login/captcha")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("login")
     * @Apidoc\Returned("Maidou-Admin-Captcha-Id",type="string",require=true,desc="header头返回参数，唯一标识码")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",
     *   @Apidoc\Returned("captcha",type="string",desc="验证码 base64")
     * )
     * @return \think\response\Json
     */
    #[Route('GET', '/login/captcha', ['slug' => 'maidou.login.captcha'])]
    public function captcha(): \think\response\Json
    {
        $captchaAttr = (new Captcha())->makeCode()->getAttr();

        return $this->success('', [
            'captcha' => Arr::get($captchaAttr, 'data', ''),
        ], 0, [
            'Maidou-Admin-Captcha-Id' => Arr::get($captchaAttr, 'uniq', ''),
        ]);
    }

    /**
     * 注解文档
     * @Apidoc\Title("登录")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/lgoin")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("login")
     * @Apidoc\Param("name", type="string", require=true, desc="账号")
     * @Apidoc\Param("password", type="string", require=true, desc="密码，需要MD5加密")
     * @Apidoc\Param("captcha", type="string", require=true, desc="验证码")
     */
    // 注解路由
    #[Route('POST', '/login/login', ['slug' => 'maidou.login.login'])]
    public function login(Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $this->validate($data, [
                'name' => 'require|alphaNum',
                'password' => 'require|alphaNum|length:32',
                'captcha' => 'require|length:4',
            ], [
                'name.require' => '账号必须',
                'name.alphaNum' => '账号错误',
                'password.require' => '密码必须',
                'password.alphaNum' => '密码错误',
                'captcha.require' => '验证码必须',
                'captcha.length' => '验证码错误',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        // 密码是否正确验证
        $admin = AdminModel::where('name', $data['name'])->find();
        if (empty($admin)) {
            return $this->error('账号错误');
        }
        $password = AdminModel::password($data['password'], $admin['password_salt']);
        if ($password !== $admin['password']) {
            return $this->error('密码错误');
        }

        // token签名
        $token = (new Token())
            ->withStorages([
                'admin_id' => $admin['id'],
                'is_root' => ((int)$admin['is_root'] === 1),
            ])
            ->getToken();

        return $this->success('success', [
            'token' => $token,
        ]);
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="退出登录", url="/login/log-out", method="DELETE", slug="maidou.login.loginOut")
     * 注解文档
     * @Apidoc\Title("退出登录")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/login-out/:id")
     * @Apidoc\Method("DELETE")
     * @Apidoc\Tag("login")
     * @Apidoc\Param("id", type="string", require=true, desc="管理员id")
     */
    // 注解路由
    #[Route('DELETE', '/login/login-out', ['slug' => 'maidou.login.loginOut'])]
    public function logOut(): \think\response\Json
    {
        $admin = AdminModel::where('id', app('auth-admin')->getId() ?? '')->find();
        if (empty($admin)) {
            return $this->error('账号错误');
        }
        try {
            (new \app\common\support\Token)->delete();
        } catch (Exception $e) {
            $this->error($e->getMessage(), ResponseCode::LOGOUT_ERROR);
        }

        return $this->success('成功');
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="登录用户信息", url="/login/info", method="GET", slug="maidou.login.info")
     * 注解文档
     * @Apidoc\Title("登录用户信息")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/login/info")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("login")
     * @Apidoc\Param("id", type="string", require=true, desc="管理员id")
     */
    // 注解路由
    #[Route('GET', '/login/info', ['slug' => 'maidou.login.info'])]
    public function info(): \think\response\Json
    {
        try {
            $row = app('auth-admin')->getData();
            if (app('auth-admin')->isRoot()) {
                $access = AuthRuleModel::field('slug')->select()->toArray();
                $slug = array_column($access, 'slug');
            } else {
                $access = AuthGroupAccessModel::where('admin_id', $row['id'])->select()->toArray();
                $access = AuthRuleModel::field('slug')
                    ->whereIn('id', array_column($access, 'rule_id'))
                    ->select()
                    ->toArray();
                $slug = array_column($access, 'slug');
            }
            $row['roles'] = $slug;
        } catch (DataNotFoundException|ModelNotFoundException|DbException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('', $row);
    }
}
