<?php

namespace app\common\support;

use app\common\traits\ResponseJson;
use think\facade\Request;

class Files
{
    use ResponseJson;

    /**
     * 上传文件
     * @param $name file
     * @return \think\response\Json
     */
    public function upload($name = 'file'): \think\response\Json
    {
        $file = request()->file($name);
        $domain = Request::domain(true);
        // 上传到本地服务器
        $saveName = \think\facade\Filesystem::disk('public')->putFile('topic', $file, 'md5');
        $url = \think\facade\Filesystem::disk('public')->url($saveName);
        // 在控制器或路由闭包函数中使用
        return $this->success('', [
            'path' => $saveName,
            'url'=>$domain.$url
        ]);
    }
}