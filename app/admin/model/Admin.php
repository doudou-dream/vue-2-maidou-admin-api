<?php
declare (strict_types=1);

namespace app\admin\model;

use app\common\support\Password;
use think\model\concern\SoftDelete;

class Admin extends Base
{
    use SoftDelete;

    protected $name = 'admin';

    /**
     * 角色
     * @return \think\model\relation\BelongsToMany
     */
    public function groups(): \think\model\relation\BelongsToMany
    {
        return $this->belongsToMany(AuthGroup::class, 'auth_group_access', 'group_id', 'admin_id');
    }

    /**
     * 创建前
     *
     * @param Admin $model
     *
     * @return mixed|void
     */
    public static function onBeforeInsert($model)
    {
        parent::onBeforeInsert($model);
        $model->id          = md5(mt_rand(100000, 999999).microtime());
        $model->create_ip   = request()->ip();
        $model->create_time = time();
        $model->last_active = time();
        $model->last_ip     = request()->ip();
    }

    /**
     * 更新前
     *
     * @param Admin $model
     *
     * @return mixed|void
     */
    public static function onBeforeUpdate($model)
    {
        parent::onBeforeUpdate($model);
        $model->last_active = time();
        $model->last_ip     = request()->ip();
    }

    /**
     * 返回密码信息
     *
     * @param $password
     * @param string $salt
     *
     * @return array|string
     */
    public static function password($password, string $salt = '')
    {
        return (new Password())->withSalt(config('maidou.passport.password_salt'))->encrypt($password, $salt);
    }

}
