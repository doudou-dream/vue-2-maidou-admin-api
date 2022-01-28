<?php

declare (strict_types=1);

namespace app\common\http;

/*
 * 响应代码
 *
 * 默认0 没有错误
 * 默认1 默认错误
 *
 * 1开头 登陆退出等错误
 * 2开头 格式等错误
 * 3开头 转移等相关
 * 4开头 数据没有找到
 * 5开头 存储修改等错误
 *
 * use:
 * \ResponseCode::SUCCESS
 *
 * @create 2020-11-1
 * @author deatil
 */

class ResponseCode
{
    const SUCCESS = 0;// 通用成功
    const ERROR   = 1;// 通用错误
    const INVALID = 999;// 服务器错误

    const LOGIN_ERROR        = 101;// 登录错误
    const LOGOUT_ERROR       = 102;// 退出错误
    const ACCESS_TOKEN_ERROR = 103;// 访问令牌错误
    const AUTH_ERROR         = 105;// 授权错误

    const EMPTY_PARAM   = 200;// 空参数
    const PARAM_INVALID = 201;// 参数无效


    const RECORD_NOT_FOUND = 501; // 记录未找到
    const DELETE_FAILED    = 502; // 记录删除失败
    const CREATE_FAILED    = 503; // 添加记录失败
    const UPDATE_FAILED    = 504; // 修改记录失败
}
