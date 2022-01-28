<?php

declare (strict_types=1);

namespace app\common\support;

/**
 * 密码
 * @create 2021年12月30日
 * @author maidou
 */
class Password
{
    protected $salt = '';

    /**
     * 设置盐
     *
     * @param string $salt 加密盐
     *
     * @return $this
     */
    public function withSalt(string $salt): Password
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * 密码加密
     *
     * @param string $password // 密码
     * @param string $salt // 传入加密串，在修改密码时做认证
     *
     * @return array|string
     */
    public function encrypt(string $password, string $salt = '')
    {
        $pwd             = [];
        $pwd['salt']     = $salt ?: $this->randomString();
        $pwd['password'] = md5(md5($password.$pwd['salt']).$this->salt);

        return $salt ? $pwd['password'] : $pwd;
    }

    /**
     * 随机字符串
     *
     * @param int type $len 字符长度
     *
     * @return string 随机字符串
     */
    protected function randomString(int $len = 6): string
    {
        $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

        return substr(str_shuffle(str_repeat($pool, intval(ceil($len / strlen($pool))))), 0, $len);
    }

}
