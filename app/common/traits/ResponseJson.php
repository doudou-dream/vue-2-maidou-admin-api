<?php

declare (strict_types=1);

namespace app\common\traits;

use app\common\http\ResponseCode;
use think\response\Json;

/**
 * 返回响应Json
 * @create 2021年12月27日
 * @author maidou
 */
trait ResponseJson
{
    /**
     * 返回成功json
     *
     * @param null $message
     * @param null $data
     * @param int $code
     * @param array $header
     *
     * @return Json
     */
    protected function success(
        $message = null,
        $data = null,
        $code = ResponseCode::SUCCESS,
        $header = []
    ): Json {
        return json([
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
        ], 200, $header);
    }

    /**
     * 返回错误json
     *
     * @param null $message
     * @param int $code
     * @param array $data
     * @param array $header
     *
     * @return Json
     */
    protected function error(
        $message = null,
        $code = ResponseCode::ERROR,
        $data = [],
        $header = []
    ): Json {
        return json([
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
        ], 200, $header);
    }

}
