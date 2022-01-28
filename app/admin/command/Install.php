<?php
declare (strict_types=1);

namespace app\admin\command;

use app\common\support\Password;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use Exception;

class Install extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('install')
            ->setDescription('maidou-admin初始化安装，需要保证database配置可以访问数据库');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $this->install();
        } catch (Exception $e) {
            $output->writeln('install error: '.$e->getMessage());

            return false;
        }
        $output->writeln('install success');

        return true;
    }

    /**
     * 安装sql
     * @return bool|string
     * @throws \Exception
     */
    protected function install($username = 'admin', $password = '123456')
    {
        $password = md5($password);//密码
        $sqlPath  = file_get_contents(root_path().'database'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'install.sql');
        $sqlArray = $this->parseSql($sqlPath, Db::getConfig('connections.mysql.prefix'), 'm_');
        Db::startTrans();
        try {
            foreach ($sqlArray as $vo) {
                Db::execute($vo);
            }
            $pwd = (new Password())->withSalt(config('maidou.passport.password_salt'))->encrypt($password);
            Db::name('admin')
                ->insert([
                    'id'            => md5(mt_rand(100000, 999999).microtime()),
                    'name'          => $username,
                    'is_root'       => 1,
                    'password'      => $pwd['password'],
                    'password_salt' => $pwd['salt'],
                    'create_time'   => time(),
                    'create_ip'     => '127.0.0.1',
                ]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }

        return true;
    }

    /**
     * sql格式化
     *
     * @param $sql
     * @param $to
     * @param $from
     *
     * @return false|string[]
     */
    protected function parseSql($sql, $to, $from)
    {
        [$pureSql, $comment] = [[], false];
        $sql = explode("\n", trim(str_replace(["\r\n", "\r"], "\n", $sql)));
        foreach ($sql as $key => $line) {
            if ($line == '') {
                continue;
            }
            if (preg_match("/^(#|--)/", $line)) {
                continue;
            }
            if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                continue;
            }
            if (substr($line, 0, 2) == '/*') {
                $comment = true;
                continue;
            }
            if (substr($line, -2) == '*/') {
                $comment = false;
                continue;
            }
            if ($comment) {
                continue;
            }
            if ($from != '') {
                $line = str_replace('`'.$from, '`'.$to, $line);
            }
            if ($line == 'BEGIN;' || $line == 'COMMIT;') {
                continue;
            }
            array_push($pureSql, $line);
        }
        $pureSql = implode("\n", $pureSql);

        return explode(";\n", $pureSql);
    }

}
