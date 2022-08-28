<?php
declare (strict_types=1);

namespace app\admin\controller\system;

use app\admin\model\Admin as AdminModel;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\common\BaseController;
use app\common\support\annotation as ApiPower;
use hg\apidoc\annotation as Apidoc;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\Request;

/**
 * 注解权限
 * @ApiPower\Power(title="账号管理", slug="maidou.admin")
 * 注解文档
 * @Apidoc\Title("账号管理")
 **/
class Admin extends BaseController
{
    /**
     * 注解权限
     * @ApiPower\Power(title="列表", url="/admin", method="GET",  slug="maidou.admin.index")
     * 注解文档
     * @Apidoc\Title("列表")
     * @Apidoc\Url("/admin")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("account")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",
     *   @Apidoc\Returned("start",type="int",desc="起始位置"),
     *   @Apidoc\Returned("limit",type="int",desc="查询数量"),
     *   @Apidoc\Returned("data",type="array",desc="返回数据"),
     *   @Apidoc\Returned("total",type="array",desc="数据总量")
     * )
     */
    public function index(Request $request)
    {
        $start  = (int)$request->param('start', 0);
        $limit  = (int)$request->param('limit', 10);
        $order  = $request->param('order', 'ASC');
        $wheres = [];
        $query  = AdminModel::where($wheres);
        $total  = $query->count();
        $data   = $query->limit($start, $limit)->order('create_time', $order)->select();

        return $this->success('', [
            'start' => $start,
            'limit' => $limit,
            'data'  => $data,
            'total' => $total,
        ]);
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="创建", url="/admin", method="POST",  slug="maidou.admin.create")
     * 注解文档
     * @Apidoc\Title("创建")
     * @Apidoc\Url("/admin/create")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("account")
     * @Apidoc\Param("name", type="string", require=true, desc="账号")
     * @Apidoc\Param("nickname", type="string", require=false, desc="昵称")
     * @Apidoc\Param("introduce", type="string", require=false, desc="简介")
     */
    public function create(Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $this->validate($data, [
                'name' => 'require|alphaNum',
            ], [
                'name.require'  => '账号必须',
                'name.alphaNum' => '账号错误',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $result = AdminModel::query()->create([
            'name'      => $data['name'],
            'nickname'  => $data['nickname'],
            'introduce' => $data['introduce'],
        ]);
        if ($result->isEmpty()) {
            return $this->error('错误');
        }

        return $this->success('成功');
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="修改密码", url="/admin/modify-password", method="PATCH",  slug="maidou.admin.modifyPassword")
     * 注解文档
     * @Apidoc\Title("修改密码")
     * @Apidoc\Url("/admin/modify-password")
     * @Apidoc\Method("PATCH")
     * @Apidoc\Tag("account")
     * @Apidoc\Param("id", type="string", require=true, desc="账号id")
     * @Apidoc\Param("password", type="string", require=true, desc="账号密码")
     */
    public function modifyPassword(Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $this->validate($data, [
                'id'       => 'require|alphaNum|length:32',
                'password' => 'require|alphaNum|length:32',
            ], [
                'id.require'        => '账号必须',
                'id.alphaNum'       => '账号错误',
                'password.require'  => '密码必须',
                'password.alphaNum' => '密码错误',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $password = AdminModel::password($data['password']);
        $result   = AdminModel::where('id', $data['id'])
            ->find()
            ->save([
                'password'      => $password['password'],
                'password_salt' => $password['salt'],
            ]);
        if ($result === false) {
            return $this->error('错误');
        }

        return $this->success('成功');
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="详情", url="/admin/:id", method="GET",  slug="maidou.admin.detail")
     * 注解文档
     * @Apidoc\Title ("详情")
     * @Apidoc\Url("/admin/:id")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("account")
     * @Apidoc\Param("id", type="string", require=true, desc="账号id")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",
     *   @Apidoc\Returned("data",type="string",desc="账号信息")
     * )
     */
    public function detail(string $id): \think\response\Json
    {
        try {
            $row           = AdminModel::where('id', $id)->find();
            $access        = AuthGroupAccessModel::where('admin_id', $row['id'])->select()->toArray();
            $row['groups'] = array_column($access, 'group_id');
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('', $row);
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="更新", url="/admin/:id", method="PUT",  slug="maidou.admin.update")
     * 注解文档
     * @Apidoc\Title("更新")
     * @Apidoc\Url("/admin/:id")
     * @Apidoc\Method("PUT")
     * @Apidoc\Tag("account")
     * @Apidoc\Param("id", type="string", require=true, desc="账号id")
     * @Apidoc\Param("name", type="string", require=true, desc="账号")
     * @Apidoc\Param("nickname", type="string", require=false, desc="昵称")
     * @Apidoc\Param("introduce", type="string", require=false, desc="简介")
     */
    public function update(string $id, Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $admin = AdminModel::where('id', $id)->find();
            if (empty($admin)) {
                return $this->error('账号不存在');
            }
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return $this->error('账号不存在');
        }

        try {
            $this->validate($data, [
                'name' => 'require|alphaNum',
            ], [
                'name.require'  => '账号必须',
                'name.alphaNum' => '账号错误',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $result = $admin
            ->save([
                'name'      => $data['name'],
                'nickname'  => $data['nickname'],
                'introduce' => $data['introduce'],
            ]);
        if (!$result) {
            return $this->error('错误');
        }

        return $this->success('成功');
    }


    /**
     * 注解权限
     * @ApiPower\Power(title="删除", url="/admin/:id", method="DELETE",  slug="maidou.admin.delete")
     * 注解文档
     * @Apidoc\Title ("删除")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/admin/:id")
     * @Apidoc\Method("DELETE")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Param("id", type="string", require=true, desc="管理员id")
     */
    public function delete(string $id): \think\response\Json
    {
        $row = AdminModel::where('id', $id)->find();
        if (!$row) {
            return $this->error('错误');
        }
        $row->delete();

        return $this->success('成功');
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="授权", url="/admin/:id", method="PATCH",  slug="maidou.admin.access")
     * 注解文档
     * @Apidoc\Title ("授权")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/admin/:id")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Param("id", type="string", require=true, desc="管理员id")
     * @Apidoc\Param("ids", type="string", require=false, desc="角色ids xxx,xxx,xxx")
     */
    public function access(string $id, Request $request): \think\response\Json
    {
        $row = AdminModel::query()->where('id', $id)->find();
        if ($row->isEmpty()) {
            return $this->error('信息不存在');
        }

        $data = $request->all();
        // 删除
        $accessArr = AuthGroupAccessModel::where('admin_id', $id)->select()->toArray();
        foreach ($accessArr as $access) {
            AuthGroupAccessModel::where('id', $access['id'])->find()->delete();
        }
        $data['ids'] = explode(',', $data['ids']) ?: [];
        $install     = [];
        foreach ($data['ids'] as $itemId) {
            $install [] = [
                'admin_id' => $id,
                'group_id' => $itemId,
            ];
        }
        try {
            AuthGroupAccessModel::query()->saveAll($install);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return $this->success('成功');
    }

    /**
     * 注解权限
     * @ApiPower\Power(title="角色列表", url="/admin/group", method="GET",  slug="maidou.admin.access")
     * 注解文档
     * @Apidoc\Title("角色列表")
     * @Apidoc\Url("/admin/group")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("authgroup")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",
     *   @Apidoc\Returned("data",type="array",desc="返回数据"),
     *   @Apidoc\Returned("total",type="array",desc="数据总量")
     * )
     */
    public function group(Request $request): \think\response\Json
    {
        $order = $request->param('order', 'ASC');
        $query = new AuthGroupModel();
        $total = $query->count();
        $data  = $query->order('create_time', $order)->select();

        return $this->success('', [
            'data'  => $data,
            'total' => $total,
        ]);
    }
}
