<?php

namespace app\common\support;

use app\common\traits\ResponseJson;

class Files
{
    use ResponseJson;
    public function upload($name='file'){
        $file = request()->file($name);
        // 上传到本地服务器
        $saveName = \think\facade\Filesystem::disk('public')->putFile( 'topic', $file, 'md5');
        return $this->success('', [
            'path' => $saveName,
        ]);
    }
}