<?php
declare (strict_types=1);

namespace app\admin\model;


class AuthRule extends Base
{
    protected $name = 'auth_rule';

    /**
     * 创建前
     *
     * @param AuthRule $model
     *
     * @return mixed|void
     */
    public static function onBeforeInsert($model)
    {
        parent::onBeforeInsert($model);
        $model->id          = md5(mt_rand(100000, 999999).microtime());
        $model->create_ip   = request()->ip();
        $model->create_time = time();
        $model->update_time = time();
        $model->update_ip   = request()->ip();
    }

    /**
     * 更新前
     *
     * @param AuthRule $model
     *
     * @return mixed|void
     */
    public static function onBeforeUpdate($model)
    {
        parent::onBeforeUpdate($model);
        $model->update_time = time();
        $model->update_ip   = request()->ip();
    }
}
