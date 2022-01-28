<?php
declare (strict_types=1);

namespace app\admin\model;

use think\model\concern\SoftDelete;

class AuthGroup extends Base
{
    use SoftDelete;

    protected $name = 'auth_group';

    /**
     * 创建前
     *
     * @param AuthGroup $model
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
     * @param AuthGroup $model
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
