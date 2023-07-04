<?php
declare (strict_types=1);

namespace app\admin\middleware;

use app\common\exception\FailException;
use app\common\http\ResponseCode;
use app\common\support\Token;
use app\common\traits\ResponseJson;
use app\admin\Auth\admin as AuthAdmin;
use app\admin\model\Admin as AdminModel;
use think\Request;
use Closure;

class Auth
{
    use ResponseJson;

    public function handle(Request $request, Closure $next)
    {
        if (!$this->shouldPassThrough($request)) {
            $this->jwtCheck();
        }

        return $next($request);
    }

    /*
     * jwt验证
     * @throws FailException
     */
    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \app\common\exception\FailException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\Exception
     */
    protected function jwtCheck()
    {
        try {
            $token = (new Token())->withRequestToken();
            $token->validate();
        } catch (\Exception $e) {
            throw new FailException($e->getMessage(), ResponseCode::ACCESS_TOKEN_ERROR);
        }
        $adminId   = $token->getClaim('admin_id');
        $adminInfo = AdminModel::with(['groups'])->where('id', $adminId)->find();
        if (empty($adminInfo)) {
            throw new FailException('帐号不存在或者已被锁定', ResponseCode::AUTH_ERROR);
        }
        $adminInfo = $adminInfo->toArray();
        // 绑定类
        bind('auth-admin', AuthAdmin::class);
        // 信息初始化
        app('auth-admin')->withId($adminId)->withData($adminInfo);
        if (!app('auth-admin')->isGroup()) {
            throw new FailException('帐号用户组不存在或者已被锁定', ResponseCode::AUTH_ERROR);
        }
    }

    /**
     * 权限过滤
     *
     * @param \think\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough(Request $request): bool
    {
        $name = request()->rule()->getOption('slug');
        if(empty($name)){
            $name = request()->rule()->getName();
        }
        // token_filter
        return in_array($name, config('maidou.auth.token_filter'));
    }
}
