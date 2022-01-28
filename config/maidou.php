<?php
# +----------------------------------------------------------------------
# | maidou配置
# +----------------------------------------------------------------------

return [
    'auth'     => [
        // token过滤
        'token_filter'      => [
            'maidou.login.login',
            'maidou.login.captcha',
        ],
        // 权限过滤
        'permission_filter' => [
            'maidou.login.login',
            'maidou.login.captcha',
            'maidou.login.loginOut',
            'maidou.login.info',
        ],
    ],
    // 密码加盐
    'passport' => [
        'password_salt' => 'doimetp3qwz6dbnpuu12v5fe845l17gk',
    ],
];
