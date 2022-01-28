<?php

ini_set('display_errors', 'On');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use think\facade\Db;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../vendor/topthink/framework/src/helper.php';

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__.DS.'..'.DS);
define('INSTALL_PATH', ROOT_PATH.'database'.DS.'install'.DS);
define('CONFIG_PATH', ROOT_PATH.'config'.DS);
define('ENV_PATH', ROOT_PATH.DS);

$currentHost = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/';

function isReadWrite($file)
{
    if (DIRECTORY_SEPARATOR == '\\') {
        return true;
    }
    if (DIRECTORY_SEPARATOR == '/' && @ ini_get("safe_mode") === false) {
        return is_writable($file);
    }
    if (!is_file($file) || ($fp = @fopen($file, "r+")) === false) {
        return false;
    }
    fclose($fp);

    return true;
}

$errorInfo = null;
if (is_file(INSTALL_PATH.'lock'.DS.'install.lock')) {
    $errorInfo = '已安装系统，如需重新安装请删除文件：/database/install/lock/install.lock';
} elseif (!isReadWrite(ROOT_PATH.'config'.DS)) {
    $errorInfo = ROOT_PATH.'config'.DS.'：读写权限不足';
} elseif (!isReadWrite(ROOT_PATH.'runtime'.DS)) {
    $errorInfo = ROOT_PATH.'runtime'.DS.'：读写权限不足';
} elseif (!isReadWrite(ROOT_PATH.'public'.DS)) {
    $errorInfo = ROOT_PATH.'public'.DS.'：读写权限不足';
} elseif (!checkPhpVersion('7.1.0')) {
    $errorInfo = 'PHP版本不能小于7.1.0';
} elseif (!extension_loaded("PDO")) {
    $errorInfo = '当前未开启PDO，无法进行安装';
}

// POST请求
if (isAjax()) {
    $post = $_POST;

    $cover      = $post['cover'] == 1 ? true : false;
    $database   = $post['database'];
    $hostname   = $post['hostname'];
    $hostport   = $post['hostport'];
    $dbUsername = $post['db_username'];
    $dbPassword = $post['db_password'];
    $prefix     = $post['prefix'];
    $adminUrl   = $post['admin_url'];
    $username   = $post['username'];
    $password   = $post['password'];

    // 参数验证
    $validateError = null;

    // 判断是否有特殊字符
    $check = preg_match('/[0-9a-zA-Z]+$/', $adminUrl, $matches);
    if (!$check) {
        $validateError = '后台地址不能含有特殊字符, 只能包含字母或数字。';
        $data          = [
            'code' => 0,
            'msg'  => $validateError,
        ];
        die(json_encode($data));
    }

    if (strlen($adminUrl) < 2) {
        $validateError = '后台的地址不能小于2位数';
    } elseif (strlen($password) < 5) {
        $validateError = '管理员密码不能小于5位数';
    } elseif (strlen($username) < 4) {
        $validateError = '管理员账号不能小于4位数';
    }
    if (!empty($validateError)) {
        $data = [
            'code' => 0,
            'msg'  => $validateError,
        ];
        die(json_encode($data));
    }

    // DB类初始化
    $config = [
        'type'     => 'mysql',
        'hostname' => $hostname,
        'username' => $dbUsername,
        'password' => $dbPassword,
        'hostport' => $hostport,
        'charset'  => 'utf8',
        'prefix'   => $prefix,
        'debug'    => true,
    ];
    Db::setConfig([
        'default'     => 'mysql',
        'connections' => [
            'mysql'   => $config,
            'install' => array_merge($config, ['database' => $database]),
        ],
    ]);

    // 检测数据库连接
    if (!checkConnect()) {
        $data = [
            'code' => 0,
            'msg'  => '数据库连接失败',
        ];
        die(json_encode($data));
    }
    // 检测数据库是否存在
    if (!$cover && checkDatabase($database)) {
        $data = [
            'code' => 0,
            'msg'  => '数据库已存在，请选择覆盖安装或者修改数据库名',
        ];
        die(json_encode($data));
    }
    // 创建数据库
    createDatabase($database);
    // 导入sql语句等等
    $install = install($username, $password, array_merge($config, ['database' => $database]));
    if ($install !== true) {
        $data = [
            'code' => 0,
            'msg'  => '系统安装失败：'.$install,
        ];
        die(json_encode($data));
    }
    $data = [
        'code' => 1,
        'msg'  => '系统安装成功，正在跳转登录页面',
        'url'  => $adminUrl,
    ];
    die(json_encode($data));
}

function isAjax()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    } else {
        return false;
    }
}

function checkPhpVersion($version)
{
    $php_version = explode('-', phpversion());
    $check       = strnatcasecmp($php_version[0], $version) >= 0 ? true : false;

    return $check;
}

function checkConnect()
{
    try {
        Db::query("select version()");
    } catch (\Exception $e) {
        return false;
    }

    return true;
}

function checkDatabase($database)
{
    $check = Db::query("SELECT * FROM information_schema.schemata WHERE schema_name='{$database}'");
    if (empty($check)) {
        return false;
    } else {
        return true;
    }
}

function createDatabase($database)
{
    try {
        Db::execute("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8");
    } catch (\Exception $e) {
        return false;
    }

    return true;
}

function parseSql($sql, $to, $from)
{
    [$pure_sql, $comment] = [[], false];
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
        array_push($pure_sql, $line);
    }
    $pure_sql = implode("\n", $pure_sql);

    return explode(";\n", $pure_sql);
}

function install($username, $password, $config)
{
    $sqlPath  = file_get_contents(INSTALL_PATH.'install.sql');
    $sqlArray = parseSql($sqlPath, $config['prefix'], 'm_');
    Db::startTrans();
    try {
        foreach ($sqlArray as $vo) {
            Db::connect('install')->execute($vo);
        }
        $pwd = password(md5($password));
        Db::connect('install')
            ->name('admin')
            ->insert([
                'id'            => md5(mt_rand(100000, 999999).microtime()),
                'name'          => $username,
                'is_root'       => 1,
                'password'      => $pwd['password'],
                'password_salt' => $pwd['salt'],
                'create_time'   => time(),
                'create_ip'     => '127.0.0.1',
            ]);

        // 处理安装文件
        !is_dir(INSTALL_PATH) && @mkdir(INSTALL_PATH);
        !is_dir(INSTALL_PATH.'lock'.DS) && @mkdir(INSTALL_PATH.'lock'.DS);
        @file_put_contents(INSTALL_PATH.'lock'.DS.'install.lock', date('Y-m-d H:i:s'));
        @file_put_contents(ENV_PATH.'.env', getEnvConfig($config));
        @file_put_contents(CONFIG_PATH.'maidou.php', getMaidouConfig());
        @file_put_contents(CONFIG_PATH.'app.php', getAppConfig());
        @file_put_contents(CONFIG_PATH.'database.php', getDatabaseConfig($config));
        Db::commit();
    } catch (\Exception $e) {
        Db::rollback();

        return $e->getMessage();
    }

    return true;
}

function password($password)
{
    $len             = 6;
    $pool            = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $str             = substr(str_shuffle(str_repeat($pool, intval(ceil($len / strlen($pool))))), 0, $len);
    $pwd             = [];
    $pwd['salt']     = $str;
    $pwd['password'] = md5(md5($password.$pwd['salt']).'doimetp3qwz6dbnpuu12v5fe845l17gk');

    return $pwd;
}

function getEnvConfig($data)
{
    return <<<EOT
APP_DEBUG = true

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = {$data['hostname']}
DATABASE = {$data['database']}
USERNAME = {$data['username']}
PASSWORD = {$data['password']}
HOSTPORT = {$data['hostport']}
CHARSET = utf8
DEBUG = true
PREFIX = {$data['prefix']}

[LANG]
default_lang = zh-cn
EOT;
}

function getMaidouConfig()
{
    return <<<EOT
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

EOT;
}

function getAppConfig()
{
    return <<<EOT
<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath().'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
];

EOT;
}

function getDatabaseConfig($data)
{
    return <<<EOT
<?php

return [
    // 默认使用的数据库连接配置
    'default'         => env('database.driver', 'mysql'),

    // 自定义时间查询规则
    'time_query_rule' => [],

    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => true,

    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',

    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'              => env('database.type', 'mysql'),
            // 服务器地址
            'hostname'          => env('database.hostname', '{$data['hostname']}'),
            // 数据库名
            'database'          => env('database.database', '{$data['database']}'),
            // 用户名
            'username'          => env('database.username', '{$data['username']}'),
            // 密码
            'password'          => env('database.password', '{$data['password']}'),
            // 端口
            'hostport'          => env('database.hostport', '{$data['hostport']}'),
            // 数据库连接参数
            'params'            => [],
            // 数据库编码默认采用utf8
            'charset'           => env('database.charset', 'utf8'),
            // 数据库表前缀
            'prefix'            => env('database.prefix', '{$data['prefix']}'),

            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'            => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'       => false,
            // 读写分离后 主服务器数量
            'master_num'        => 1,
            // 指定从服务器序号
            'slave_no'          => '',
            // 是否严格检查字段是否存在
            'fields_strict'     => true,
            // 是否需要断线重连
            'break_reconnect'   => false,
            // 监听SQL
            'trigger_sql'       => env('app_debug', true),
            // 开启字段缓存
            'fields_cache'      => false,
            // 字段缓存路径
            'schema_cache_path' => app()->getRuntimePath() . 'schema' . DIRECTORY_SEPARATOR,
        ],

        // 更多的数据库配置信息
    ],
];

EOT;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>安装EasyAdmin后台程序</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" type="text/css" href="https://www.layuicdn.com/layui-v2.5.6/css/layui.css"/>
    <link rel="stylesheet" href="static/css/install.css" media="all">
</head>
<body>
<!--<h1><img src="static/common/images/logo-1.png"></h1>-->
<div style="margin-top: 50px;width:100vh"></div>
<h2>安装系统</h2>
<div class="content">
    <p class="desc">
        有问题请在gitee进行留言
    </p>
    <form class="layui-form layui-form-pane" action="">
        <?php if ($errorInfo): ?>
            <div class="error">
                <?php echo $errorInfo; ?>
            </div>
        <?php endif; ?>
        <div class="bg">
            <div class="layui-form-item">
                <label class="layui-form-label">数据库地址</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="hostname" autocomplete="off" lay-verify="required"
                           lay-reqtext="请输入数据库地址" placeholder="请输入数据库地址" value="127.0.0.1">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">数据库端口</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="hostport" autocomplete="off" lay-verify="required"
                           lay-reqtext="请输入数据库端口" placeholder="请输入数据库端口" value="3306">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">数据库名称</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="database" autocomplete="off" lay-verify="required"
                           lay-reqtext="请输入数据库名称" placeholder="请输入数据库名称" value="maidou">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">数据表前缀</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="prefix" autocomplete="off" lay-verify="required"
                           lay-reqtext="请输入数据表前缀" placeholder="请输入数据表前缀" value="m_">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">数据库账号</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="db_username" autocomplete="off" lay-verify="required"
                           lay-reqtext="请输入数据库账号" placeholder="请输入数据库账号" value="root">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">数据库密码</label>
                <div class="layui-input-block">
                    <input type="password" class="layui-input" name="db_password" autocomplete="off"
                           lay-reqtext="请输入数据库密码" placeholder="请输入数据库密码">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">覆盖数据库</label>
                <div class="layui-input-block" style="text-align: left">
                    <input type="radio" name="cover" value="1" title="覆盖">
                    <input type="radio" name="cover" value="0" title="不覆盖" checked>
                </div>
            </div>
        </div>
        <div class="bg">
            <div class="layui-form-item">
                <label class="layui-form-label">后台的地址</label>
                <div class="layui-input-block">
                    <input class="layui-input" id="admin_url" name="admin_url" autocomplete="off" lay-verify="required"
                           lay-reqtext="请输入后台的地址" placeholder="为了后台安全，不建议将后台路径设置为admin" value="admin">
                    <span class="tips">后台登录地址： <?php echo $currentHost; ?><span id="admin_name">admin.html</span></span>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">管理员账号</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="username" autocomplete="off" lay-verify="required"
                           lay-reqtext="请输入管理员账号" placeholder="请输入管理员账号" value="admin">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">管理员密码</label>
                <div class="layui-input-block">
                    <input type="password" class="layui-input" name="password" autocomplete="off" lay-verify="required"
                           lay-reqtext="请输入管理员密码" placeholder="请输入管理员密码">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <button
                    class="layui-btn layui-btn-normal <?php echo $errorInfo ? 'layui-btn-disabled' : '' ?>"
                    lay-submit=""
                    lay-filter="install">
                确定安装
            </button>
        </div>
    </form>
</div>
<script src="https://www.layuicdn.com/layui-v2.5.6/layui.js" charset="utf-8"></script>
<script>
    layui.use(['form', 'layer'], function () {
        var $ = layui.jquery,
            form = layui.form,
            layer = layui.layer;

        $("#admin_url").bind("input propertychange", function () {
            var val = $(this).val();
            $("#admin_name").text(val);
        });

        form.on('submit(install)', function (data) {
            if ($(this).hasClass('layui-btn-disabled')) {
                return false;
            }
            var _data = data.field;
            var loading = layer.msg('正在安装...', {
                icon: 16,
                shade: 0.2,
                time: false
            });
            $.ajax({
                url: window.location.href,
                type: 'post',
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                dataType: "json",
                data: _data,
                timeout: 60000,
                success: function (data) {
                    layer.close(loading);
                    if (data.code === 1) {
                        layer.msg(data.msg, {icon: 1}, function () {
                            window.location.href = location.protocol + '//' + location.host + '/' + data.url;
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                },
                error: function (xhr, textstatus, thrown) {
                    layer.close(loading);
                    layer.msg('Status:' + xhr.status + '，' + xhr.statusText + '，请稍后再试！', {icon: 2});
                    return false;
                }
            });
            return false;
        });
    });
</script>
</body>
</html>
