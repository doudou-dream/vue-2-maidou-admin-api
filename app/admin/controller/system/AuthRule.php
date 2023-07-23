<?php
declare (strict_types=1);

namespace app\admin\controller\system;

use app\admin\middleware\Auth;
use app\admin\middleware\Permission;
use app\admin\model\AuthRule as AuthRuleModel;
use app\common\BaseController;
use app\common\support\annotation as ApiPower;
use app\common\support\Tree;
use hg\apidoc\annotation as Apidoc;
use think\annotation\route\Middleware;
use think\annotation\route\Pattern;
use think\annotation\route\Route;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\middleware\AllowCrossDomain;
use think\Request;

/**
 * 注解权限
 * @ApiPower\Power(title="权限", slug="maidou.rule")
 * 注解文档
 * @Apidoc\Title("权限")
 **/
//注册路由中间件
#[Middleware([Auth::class, Permission::class, AllowCrossDomain::class])]
class AuthRule extends BaseController
{
    /**
     * 注解权限
     * @ApiPower\Power(title="列表", url="/rule", method="GET",  slug="maidou.rule.index")
     * 注解文档
     * @Apidoc\Title("列表")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/rule")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",
     *   @Apidoc\Returned("data",type="array",desc="返回数据"),
     *   @Apidoc\Returned("total",type="array",desc="数据总量")
     * )
     */
    #[Route('GET', '/rule', ['slug' => 'maidou.rule.index'])]
    public function index(Request $request): \think\response\Json
    {
        $order  = $request->param('order', 'ASC');
        $wheres = [];
        $query  = AuthRuleModel::where($wheres);
        $total  = $query->count();
        $data   = $query->order('create_time', $order)->select()->toArray();
        $Tree   = new Tree();
        $list   = $Tree
            ->withConfig('buildChildKey', 'children')
            ->withData($data)
            ->build(0);

        return $this->success('', [
            'data'  => $list,
            'total' => $total,
        ]);
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="创建", url="/rule", method="POST",  slug="maidou.rule.create")
     * 注解文档
     * @Apidoc\Title("创建")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/rule/create")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Param("parent_id", type="string", require=true, desc="父id")
     * @Apidoc\Param("title", type="string", require=true, desc="名称")
     * @Apidoc\Param("url", type="string", require=true, desc="路由")
     * @Apidoc\Param("method", type="string", require=true, desc="请求方式")
     * @Apidoc\Param("slug", type="string", require=true, desc="规则")
     * @Apidoc\Param("description", type="string", require=false, desc="简介")
     * @Apidoc\Param("listorder", type="string", require=false, desc="排序")
     * @Apidoc\Param("is_need_auth", type="string", require=false, desc="是否需要权限")
     * @Apidoc\Param("is_system", type="string", require=false, desc="是否是系统权限")
     */
    #[Route('POST', '/rule', ['slug' => 'maidou.rule.create'])]
    public function create(Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $this->validate($data, [
                'parent_id' => 'require',
                'title'     => 'require',
                'url'       => 'require',
                'method'    => 'require|alphaNum',
                'slug'      => 'require',
            ], [
                'parent_id.require' => '父id必须',
                'title.require'     => '名称必须',
                'url.require'       => 'url必须',
                'method.require'    => '请求方式必须',
                'slug.require'      => '规则名称必须',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $result = AuthRuleModel::query()->create([
            'parent_id'    => $data['parent_id'],
            'title'        => $data['title'],
            'url'          => $data['url'],
            'method'       => $data['method'],
            'slug'         => $data['slug'],
            'description'  => $data['description'] ?? '',
            'listorder'    => $data['listorder'] ?? 100,
            'is_need_auth' => $data['is_need_auth'] ?? 0,
            'is_system'    => $data['is_system'] ?? 0,
        ]);
        if ($result->isEmpty()) {
            return $this->error('错误');
        }

        return $this->success('成功');
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="详情", url="/rule/:id", method="GET",  slug="maidou.rule.detail")
     * 注解文档
     * @Apidoc\Title("详情")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/rule/:id")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Param ("id", type="string", require=true, desc="权限id")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",
     *   @Apidoc\Returned("data",type="string",desc="权限信息")
     * )
     */
    #[Route('GET', '/rule/:id', ['slug' => 'maidou.rule.detail'])]
    #[Pattern('id', '[A-Za-z0-9]{32}')] // 规则
    public function detail($id): \think\response\Json
    {
        try {
            $row = AuthRuleModel::query()->where('id', $id)->find();
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('', $row);
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="更新", url="/rule/:id", method="PUT",  slug="maidou.rule.update")
     * 注解文档
     * @Apidoc\Title("更新")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/rule/:id")
     * @Apidoc\Method("PUT")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Param("id", type="string", require=true, desc="权限id")
     * @Apidoc\Param("parent_id", type="string", require=true, desc="父id")
     * @Apidoc\Param("title", type="string", require=true, desc="名称")
     * @Apidoc\Param("url", type="string", require=true, desc="路由")
     * @Apidoc\Param("method", type="string", require=true, desc="请求方式")
     * @Apidoc\Param("slug", type="string", require=true, desc="规则")
     * @Apidoc\Param("description", type="string", require=false, desc="简介")
     * @Apidoc\Param("listorder", type="string", require=false, desc="排序")
     * @Apidoc\Param("is_need_auth", type="string", require=false, desc="是否需要权限")
     * @Apidoc\Param("is_system", type="string", require=false, desc="是否是系统权限")
     */
    #[Route('PUT', '/rule/:id', ['slug' => 'maidou.rule.update'])]
    #[Pattern('id', '[A-Za-z0-9]{32}')] // 规则
    public function update(string $id, Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $authRule = AuthRuleModel::where('id', $id)->find();
            if ($authRule->isEmpty()) {
                return $this->error('权限不存在');
            }
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return $this->error('权限不存在');
        }

        try {
            $this->validate($data, [
                'parent_id' => 'require',
                'title'     => 'require',
                'url'       => 'require',
                'method'    => 'require|alphaNum',
                'slug'      => 'require',
            ], [
                'parent_id.require' => '父id必须',
                'title.require'     => '名称必须',
                'url.require'       => 'url必须',
                'method.require'    => '请求方式必须',
                'slug.require'      => '规则名称必须',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $result = $authRule
            ->save([
                'parent_id'    => $data['parent_id'],
                'title'        => $data['title'],
                'url'          => $data['url'],
                'method'       => $data['method'],
                'slug'         => $data['slug'],
                'description'  => $data['description'] ?? '',
                'listorder'    => $data['listorder'] ?? 100,
                'is_need_auth' => $data['is_need_auth'] ?? 0,
                'is_system'    => $data['is_system'] ?? 0,
            ]);
        if (!$result) {
            return $this->error('错误');
        }

        return $this->success('成功');
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="删除", url="/rule/:id", method="DELETE",  slug="maidou.rule.delete")
     * 注解文档
     * @Apidoc\Title ("删除")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/rule/:id")
     * @Apidoc\Method("DELETE")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Param("id", type="string", require=true, desc="权限id")
     */
    #[Route('DELETE', '/rule/:id', ['slug' => 'maidou.rule.delete'])]
    #[Pattern('id', '[A-Za-z0-9]{32}')] // 规则
    public function delete(string $id): \think\response\Json
    {
        $row = AuthRuleModel::where('id', $id)->find();
        if (!$row) {
            return $this->error('错误');
        }
        $row->delete();

        return $this->success('成功');
    }
}
