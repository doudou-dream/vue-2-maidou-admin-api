<?php
declare (strict_types=1);

namespace app\common\exception;


use app\common\traits\ResponseJson;
use think\exception\Handle;
use think\Request;
use think\Response;
use Throwable;

class ExceptionHandle extends Handle
{
    use ResponseJson;

    /**
     * Render an exception into an HTTP response.
     * @access public
     *
     * @param Request $request
     * @param Throwable $e
     *
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // error json返回
        if ($e instanceof FailException) {
            return $this->error($e->getMessage(), $e->getCode(), $e->getData(), $e->getHeader());
        }
        // success json 返回
        if ($e instanceof CorrectException) {
            return $this->success($e->getMessage(), $e->getData(), $e->getCode(), $e->getHeader());
        }

        return parent::render($request, $e);
    }
}
