<?php
declare (strict_types=1);

namespace app\admin\model;

use tauthz\facade\Enforcer;

/**
 * 权限管理
 */
class AuthRuleAccess extends Base
{

    protected $name = 'auth_rule_access';

    /**
     * 规则
     */
    public function rule(): \think\model\relation\HasOne
    {
        return $this->hasOne(AuthRule::class, 'id', 'rule_id');
    }

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
        $rule = $model->rule;
        if (!empty($rule)) {
            Enforcer::addPolicy($model->group_id, $rule['slug'], $rule['method']);
        }
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
        $rule = $model->rule;
        if (!empty($rule)) {
            Enforcer::deletePermissionForUser($model->group_id, $rule['slug'], $rule['method']);
        }
    }
}
