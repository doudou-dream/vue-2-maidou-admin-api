<?php
declare (strict_types=1);

namespace app\admin\controller\system;

use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\model\AuthRuleAccess as AuthRuleAccessModel;
use app\common\BaseController;
use app\common\support\Tree;
use hg\apidoc\annotation as Apidoc;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\Request;

/**
 * @Apidoc\Title("角色")
 **/
class AuthGroup extends BaseController
{
    /**
     * @Apidoc\Title("列表")
     * @Apidoc\Url("/group")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("authgroup")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",default="null",
     *   @Apidoc\Returned("start",type="int",default="0",desc="起始位置"),
     *   @Apidoc\Returned("limit",type="int",default="10",desc="查询数量"),
     *   @Apidoc\Returned("data",type="array",default="[]",desc="返回数据"),
     *   @Apidoc\Returned("total",type="array",default="0",desc="数据总量")
     * )
     */
    public function index(Request $request): \think\response\Json
    {
        $start  = (int)$request->param('start', 0);
        $limit  = (int)$request->param('limit', 10);
        $order  = $request->param('order', 'ASC');
        $wheres = [];
        $query  = AuthGroupModel::where($wheres);
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
     * @Apidoc\Title("创建")
     * @Apidoc\Url("/group/create")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("authgroup")
     * @Apidoc\Param("title", type="string", require=true, desc="名称")
     * @Apidoc\Param("description", type="string", require=false, desc="简介")
     * @Apidoc\Param("listorder", type="int", require=false, desc="排序")
     */
    public function create(Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $this->validate($data, [
                'title' => 'require',
            ], [
                'title.require' => '名称必须',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $result = AuthGroupModel::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? '',
            'listorder'   => $data['listorder'] ?? 100,
        ]);
        if ($result->isEmpty()) {
            return $this->error('错误');
        }

        return $this->success('成功');
    }

    /**
     * @Apidoc\Title ("详情")
     * @Apidoc\Url("/group/:id")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("authgroup")
     * @Apidoc\Param ("id", type="string", require=true, desc="角色id")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",
     *   @Apidoc\Returned("data",type="string",desc="信息")
     * )
     */
    public function detail($id): \think\response\Json
    {
        try {
            $row                = AuthGroupModel::where('id', $id)->find();
            $ruleAccess         = AuthRuleAccessModel::where('group_id', $row['id'])->select()->toArray();
            $row['rule_access'] = array_column($ruleAccess, 'rule_id');
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('', $row);
    }

    /**
     * @Apidoc\Title("更新")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/group/:id")
     * @Apidoc\Method("PUT")
     * @Apidoc\Tag("authgroup")
     * @Apidoc\Param("id", type="string", require=true, desc="角色id")
     * @Apidoc\Param("title", type="string", require=true, desc="名称")
     * @Apidoc\Param("description", type="string", require=false, desc="简介")
     * @Apidoc\Param("listorder", type="int", require=false, desc="排序")
     */
    public function update(string $id, Request $request): \think\response\Json
    {
        $data = $request->all();
        try {
            $authGroup = AuthGroupModel::where('id', $id)->find();
            if (!$authGroup) {
                return $this->error('角色不存在');
            }
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return $this->error('角色不存在');
        }

        try {
            $this->validate($data, [
                'title' => 'require',
            ], [
                'title.require' => '名称必须',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $result = $authGroup
            ->save([
                'title'       => $data['title'],
                'description' => $data['description'] ?? '',
                'listorder'   => $data['listorder'] ?? 100,
            ]);
        if (!$result) {
            return $this->error('错误');
        }

        return $this->success('成功');
    }

    /**
     * @Apidoc\Title ("授权")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/group/:id")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("authgroup")
     * @Apidoc\Param("id", type="string", require=true, desc="角色id")
     * @Apidoc\Param("ids", type="string", require=true, desc="权限ids xxx,xxx,xxx")
     */
    public function access(string $id, Request $request): \think\response\Json
    {
        $row = AuthGroupModel::query()->where('id', $id)->find();
        if ($row->isEmpty()) {
            return $this->error('信息错误');
        }
        $data = $request->all();
        // 删除
        $accessArr = AuthRuleAccessModel::where('group_id', $id)->select()->toArray();
        foreach ($accessArr as $access) {
            AuthRuleAccessModel::where('id', $access['id'])->find()->delete();
        }
        $data['ids'] = explode(',', $data['ids']) ?: [];
        $install     = [];
        foreach ($data['ids'] as $itemId) {
            $install[] = [
                'group_id' => $id,
                'rule_id'  => $itemId,
            ];
        }
        try {
            $res = (new AuthRuleAccessModel())->saveAll($install);
            if (empty($res)) {
                throw new \Exception('错误');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('成功');
    }


    /**
     * @Apidoc\Title ("删除")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/group/:id")
     * @Apidoc\Method("DELETE")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Param("id", type="string", require=true, desc="角色id")
     */
    public function delete(string $id): \think\response\Json
    {
        $row = AuthGroupModel::where('id', $id)->find();
        if (!$row) {
            return $this->error('错误');
        }
        $row->delete();

        return $this->success('成功');
    }

    /**
     * @Apidoc\Title("权限列表")
     * @Apidoc\Desc("")
     * @Apidoc\Url("/group/rule")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("authrule")
     * @Apidoc\Returned("data", type="array", desc="业务数据",replaceGlobal="true",
     *   @Apidoc\Returned("data",type="array",desc="返回数据"),
     *   @Apidoc\Returned("total",type="array",desc="数据总量")
     * )
     */
    public function rule(Request $request): \think\response\Json
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
}
