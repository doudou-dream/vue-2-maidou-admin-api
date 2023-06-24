<?php
declare (strict_types=1);

namespace app\common\support;

use app\common\http\ResponseCode;
use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use think\Exception;
class Token
{
    // 过期缓存储存
    const DELETE_TOKEN = 'delete_token';
    protected $accessToken = '';// token信息
    // 存储字段名称
    protected $storages = [
        'admin_id' => '',
        'is_root'  => false,
    ];

    //配置属性
    protected $config = [
        'id'       => 'mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=', //token的唯一标识
        'issuer'   => 'admin-api.maidou.com', //签发人
        'audience' => '', //接收人 魔术方法 __construct 动态设置
        'sign'     => 'maidou', //签名密钥
        'expire'   => 3600 * 24 //有效期  86400
    ];

    public function __construct()
    {
        $this->config['audience'] = !app()->runningInConsole() ? md5(request()->server('HTTP_USER_AGENT')) : '';
    }

    /**
     * 设置数组
     *
     * @param $name
     * @param string $value
     *
     * @return $this
     */
    public function withConfig($name, string $value = ''): Token
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->withConfig($k, $v);
            }

            return $this;
        }

        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }

        return $this;
    }

    /**
     * 缓存数据设置
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function withStorages($name, $value = ''): Token
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->withStorages($k, $v);
            }

            return $this;
        }

        $this->storages[$name] = $value;

        return $this;
    }

    /**
     * 设置token令牌
     * @throws \think\Exception
     */
    public function withRequestToken($authorization = ''): Token
    {
        $authorization = $authorization ?: request()->header('Authorization');
        if (!$authorization) {
            throw new Exception('token不能为空', ResponseCode::ACCESS_TOKEN_ERROR);
        }

        $authorizationArr = explode(' ', $authorization);
        if (count($authorizationArr) != 2) {
            throw new Exception('token不能为空', ResponseCode::ACCESS_TOKEN_ERROR);
        }
        if ($authorizationArr[0] != 'Bearer') {
            throw new Exception('token格式错误', ResponseCode::ACCESS_TOKEN_ERROR);
        }

        $this->accessToken = $authorizationArr[1];
        if (!$this->accessToken) {
            throw new Exception('token不能为空', ResponseCode::ACCESS_TOKEN_ERROR);
        }

        if (count(explode('.', $this->accessToken)) <> 3) {
            throw new Exception('token格式错误', ResponseCode::ACCESS_TOKEN_ERROR);
        }

        return $this;
    }

    /**
     * 获取token令牌
     */
    public function getRequestToken(): string
    {
        return $this->accessToken;
    }


    /**
     * @throws \think\Exception
     */
    protected function getJWTToken(): \Lcobucci\JWT\Token
    {
        try {
            //解析token
            return $this->getConfiguration()->parser()->parse((string)$this->accessToken);
        } catch (\Exception $e) {
            // list of constraints violation exceptions:
            throw new Exception('token解析错误');
        }
    }

    /**
     * 生成配置对象
     * @return \Lcobucci\JWT\Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($this->config['id'])
        );
    }


    /**
     * 生成token
     *
     * @param $id
     * @param bool $isRoot
     *
     * @return string
     */
    public function getToken(): string
    {
        $config = $this->getConfiguration();
        $now    = new DateTimeImmutable();

        $token = $config->builder()
            ->issuedBy($this->config['issuer'])
            //接收人
            ->permittedFor($this->config['audience'])
            //唯一标志
            ->identifiedBy($this->config['id'])
            //签发时间
            ->issuedAt($now)
            //生效时间（立即生效:签发时间前一秒）
            ->canOnlyBeUsedAfter($now->modify('-1 second'))
            //过期时间
            ->expiresAt($now->modify("+{$this->config['expire']} second"));
        //存储数据
        foreach ($this->storages as $key => $val) {
            $token = $token->withClaim($key, $val);
        }

        //签名
        return $token->getToken($config->signer(), $config->signingKey())->toString();
    }


    /**
     * token 注销处理
     *
     * @param null $token
     *
     * @throws \think\Exception
     */
    public function delete($token = null)
    {
        $deleteToken   = cache(self::DELETE_TOKEN);
        $deleteToken[] = $token ?: $this->accessToken;
        cache(self::DELETE_TOKEN, $deleteToken);
    }

    /**
     * 获取token存储数据，默认获取当前token存储的 self::ADMIN_ID
     * @return mixed|null
     * @throws \think\Exception
     */
    public function getClaim($name = 'admin_id')
    {
        return $this->getJWTToken()->claims()->get($name);
    }

    /**
     * token的校验
     * @return object
     * @throws \think\Exception
     */
    public function validate()
    {
        if (empty($this->accessToken)) {
            throw new Exception('token解析错误');
        }
        //注销token逻辑
        $deleteToken = cache(self::DELETE_TOKEN) ?: [];
        if (in_array($this->accessToken, $deleteToken)) {
            //token已被删除（注销）
            throw new Exception('token解析错误');
        }
        //验证签发人
        $configuration = $this->getConfiguration();
        $jwtToken      = $this->getJWTToken();
        $issued        = new IssuedBy($this->config['issuer']);
        if (!$configuration->validator()->validate($jwtToken, $issued)) {
            throw new Exception('签发异常');
        }
        //验证接收
        $audience = new PermittedFor($this->config['audience']);
        if (!$configuration->validator()->validate($jwtToken, $audience)) {
            throw new Exception('接收异常');
        }
        //验证是否过期
        $timezone = new DateTimeZone('Asia/Shanghai');
        $now      = new SystemClock($timezone);
        $valid_at = new StrictValidAt($now);
        if (!$configuration->validator()->validate($jwtToken, $valid_at)) {
            throw new Exception('token过期');
        }

        return $this;
    }
}
