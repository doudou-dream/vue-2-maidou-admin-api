<?php

namespace app\common\support\annotation;


use Doctrine\Common\Annotations\Annotation\Attributes;

/**
 * 权限注解类
 * @Annotation
 * @Target({"METHOD","CLASS"})
 * @Attributes({
 *   @Attribute("time", type = "int")
 * })
 */
final class Power
{

    /**
     * 名称
     * @Required()
     * @var string
     */
    public $title;

    /**
     * 链接
     * @var string
     */
    public $url = '#';

    /**
     * method 请求方式
     * @Enum({"GET","POST","PUT","DELETE","PATCH","OPTIONS","HEAD"})
     * @var string
     */
    public $method = 'OPTIONS';

    /**
     * 权限名字
     * @var string
     */
    public $slug = '';

    /**
     * 描述
     * @var string
     */
    public $desc = '';

    /**
     * 排序
     * @var int
     */
    public $listorder = 100;
}
