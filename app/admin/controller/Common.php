<?php

namespace app\admin\controller;

use app\common\support\Files;

class Common
{
    public function upload()
    {
        return (new Files())->upload();
    }
}