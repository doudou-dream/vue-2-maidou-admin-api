<?php
declare (strict_types=1);

namespace app\admin\model;

use app\common\exception\FailException;
use app\common\traits\ResponseJson;
use Exception;
use think\Model;

class Base extends Model
{
    use ResponseJson;

    protected $deleteTime = 'delete_time';
    //私有属性，用于保存实例
    protected static $instance;

    /**
     * 单例模式
     */
    public static function query()
    {
        //判断实例有无创建，没有的话创建实例并返回，有的话直接返回
        if ((static::$instance[static::class] ?? []) instanceof static) {
            return static::$instance[static::class];
        }
        static::$instance[static::class] = new static();

        return static::$instance[static::class];
    }

    /**
     * 更新前
     * @return mixed|void
     * @throws Exception
     */
    public static function onBeforeUpdate($model)
    {
        if (config('app.demo_station')) {
            throw new FailException('演示站禁止修改信息');
        }
    }

    /**
     * 写入前
     * @return mixed|void
     * @throws Exception
     */
    public static function onBeforeWrite($model)
    {
        if (config('app.demo_station')) {
            throw new FailException('演示站禁止修改信息');
        }
    }

    /**
     * 删除前
     * @return mixed|void
     * @throws Exception
     */
    public static function onBeforeDelete($model)
    {
        if (config('app.demo_station')) {
            throw new FailException('演示站禁止修改信息');
        }
    }

    /**
     * 新增前
     * @return mixed|void
     * @throws Exception
     */
    public static function onBeforeInsert($model)
    {
        if (config('app.demo_station')) {
            throw new FailException('演示站禁止修改信息');
        }
    }
}
