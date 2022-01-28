<?php
declare (strict_types=1);

namespace app\admin\controller\system;

use app\admin\model\Admin as AdminModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\admin\model\AuthRule as AuthRuleModel;
use app\common\BaseController;
use app\common\http\ResponseCode;
use app\common\support\Captcha;
use app\common\support\Token;
use Exception;
use hg\apidoc\annotation as Apidoc;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\helper\Arr;
use think\Request;

/**
 * @Apidoc\Title("登录")
 **/
class Login extends BaseController
{
    /**
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
     * @Apidoc\Title("登录")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/lgoin")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("login")
     * @Apidoc\Param("name", type="string", require=true, desc="账号")
     * @Apidoc\Param("password", type="string", require=true, desc="密码，需要MD5加密")
     * @Apidoc\Param("captcha", type="string", require=true, desc="验证码")
     */
    public function login(Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $this->validate($data, [
                'name'     => 'require|alphaNum',
                'password' => 'require|alphaNum|length:32',
                'captcha'  => 'require|length:4',
            ], [
                'name.require'      => '账号必须',
                'name.alphaNum'     => '账号错误',
                'password.require'  => '密码必须',
                'password.alphaNum' => '密码错误',
                'captcha.require'   => '验证码必须',
                'captcha.length'    => '验证码错误',
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
                'is_root'  => ((int)$admin['is_root'] === 1),
            ])
            ->getToken();

        return $this->success('success', [
            'token' => $token,
        ]);
    }

    /**
     * @Apidoc\Title("退出登录")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/login-out/:id")
     * @Apidoc\Method("DELETE")
     * @Apidoc\Tag("login")
     * @Apidoc\Param("id", type="string", require=true, desc="管理员id")
     */
    public function logOut(): \think\response\Json
    {
        $admin = AdminModel::where('id', app('auth-admin')->getId() ?? '')->find();
        if (empty($admin)) {
            return $this->error('账号错误');
        }
        try {
            Token::delete();
        } catch (Exception $e) {
            $this->error($e->getMessage(), ResponseCode::LOGOUT_ERROR);
        }

        return $this->success('成功');
    }

    /**
     * @Apidoc\Title("登录用户信息")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/login/info")
     * @Apidoc\Method("get")
     * @Apidoc\Tag("login")
     * @Apidoc\Param("id", type="string", require=true, desc="管理员id")
     */
    public function info(): \think\response\Json
    {
        try {
            $row = app('auth-admin')->getData();
            if (app('auth-admin')->isRoot()) {
                $access = AuthRuleModel::field('slug')->select()->toArray();
                $slug   = array_column($access, 'slug');
            } else {
                $access = AuthGroupAccessModel::where('admin_id', $row['id'])->select()->toArray();
                $access = AuthRuleModel::field('slug')
                    ->whereIn('id', array_column($access, 'rule_id'))
                    ->select()
                    ->toArray();
                $slug   = array_column($access, 'slug');
            }
            $row['roles'] = $slug;
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('', $row);
    }
}
