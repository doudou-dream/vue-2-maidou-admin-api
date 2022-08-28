<?php

namespace app\common\support\annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * 描述（优先级高）
 * @package hg\apidoc\annotation
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
class Desc extends Annotation
{}
