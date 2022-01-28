<?php
declare (strict_types=1);

namespace app\common\exception;


use app\common\http\ResponseCode;
use Exception;

/**
 * 返回错误状态
 */
class FailException extends Exception
{
    // 错误码
    protected $code = ResponseCode::ERROR;
    // 错误信息
    protected $message = '参数错误';
    // 数据
    protected $data = [];
    // header 参数
    protected $header = [];

    /**
     * 初始化错误参数
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @param array|mixed $header
     */
    public function __construct(string $message, int $code = ResponseCode::ERROR, $data = [], array $header = [])
    {
        parent::__construct($message, $code);
        $this->code    = $code;
        $this->message = $message;
        $this->data    = $data;
        $this->header  = $header;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getHeader()
    {
        return $this->data;
    }
}
