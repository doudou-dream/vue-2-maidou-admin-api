<?php
declare (strict_types=1);

namespace app\admin\middleware;

use app\common\http\ResponseCode;
use app\common\support\Token;
use app\common\traits\ResponseJson;
use tauthz\facade\Enforcer;
use Exception;
use think\exception\HttpException;
use think\Request;
use Closure;

/*
 * 权限检测
 *
 * @create 2020-10-28
 * @author deatil
 */

class Permission
{
    use ResponseJson;


    public function handle($request, Closure $next)
    {
        if (!$this->shouldPassThrough($request)) {
            try {
                $this->permissionCheck();
            } catch (HttpException $e) {
                return $this->error($e->getMessage(), $e->getStatusCode());
            }
        }

        return $next($request);
    }

    /*
     * 权限检测
     */
    public function permissionCheck()
    {
        try {
            $adminId = (new Token())->withRequestToken()->getClaim('admin_id') ?? '';
        } catch (Exception $e) {
            throw new HttpException(ResponseCode::AUTH_ERROR, $e->getMessage());
        }
        $requestUrl    = request()->rule()->getName() ?? '';
        $requestMethod = request()->method() ?? '';
        if (!Enforcer::enforce($adminId, $requestUrl, $requestMethod)) {
            throw new HttpException(ResponseCode::AUTH_ERROR, '你没有访问权限');
        }
    }

    /**
     * @return bool
     */
    protected function shouldPassThrough($request): bool
    {
        // permission_filter
        if (in_array(request()->rule()->getName(), config('maidou.auth.permission_filter')) ||
            app('auth-admin')->isRoot()) {
            return true;
        }

        return false;
    }
}
