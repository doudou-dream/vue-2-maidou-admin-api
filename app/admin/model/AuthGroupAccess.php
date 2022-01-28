<?php
declare (strict_types=1);

namespace app\admin\model;

use tauthz\facade\Enforcer;

/**
 * 角色管理
 */
class AuthGroupAccess extends Base
{

    protected $name = 'auth_group_access';

    /**
     * 创建前
     *
     * @param \think\Model $model
     *
     * @return mixed|void
     */
    public static function onBeforeInsert($model)
    {
        parent::onBeforeInsert($model);
        $model->id = md5(mt_rand(100000, 999999).microtime());
    }

    /**
     * 创建后
     *
     * @param \think\Model $model
     *
     * @return mixed|void
     */
    public static function onAfterInsert($model)
    {
        Enforcer::addRoleForUser($model->admin_id, $model->group_id);
    }

    /**
     * 删除前
     *
     * @param \think\Model $model
     *
     * @return mixed|void
     */
    public static function onBeforeDelete($model)
    {
        parent::onBeforeDelete($model);
        Enforcer::deleteRoleForUser($model->admin_id, $model->group_id);
    }
}
