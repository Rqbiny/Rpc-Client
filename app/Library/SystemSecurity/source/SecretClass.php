<?php
/**
 * Created by PhpStorm.
 * CustomerModel: xdl
 * Date: 16/8/9
 * Time: 下午8:44
 */
namespace App\Library\SystemSecurity\source;


class SecretClass
{
    protected $configfile;                      //注意::默认为空,laravel框架会自动加载配置文件;其他框架则需要赋值为绝对路径————为配置文件的绝对路径

    protected $config;                          //配置文件数据

    public function __construct()
    {
        //1. 加载配置文件信息;
        $this->getconfig();
    }

    //加载系统加密库的配置文件的信息
    protected function getconfig()
    {
        //1. 判断是否是使用laravel框架
        if (empty($this->configfile)) {
            $this->configfile = base_path('config/mySystemSecret.php');
        }

        //2. 判断文件是否存在
        if (!file_exists($this->configfile)) {
            var_dump("php无权限新建配置文件,请新建配置文件,本库默认的配置文件名为" . $this->configfile . ";并且此文件返回一个数组(return [ ] )");
            exit;
        }

        //3. 读取配置文件信息
        $this->config = include($this->configfile);
    }


    /**
     * 密码加密 共两种(不可逆)
     *
     * @param       string $password 需要加密的密码
     *
     * return       string                          加密后字符串
     */
    public function passwordSecret1($password)
    {
        return md5(crypt(sha1($password), substr(sha1($password), 0, 10)));
    }

    public function passwordSecret2($password)
    {
        return md5(crypt(md5($password), substr(md5($password), 0, 10)));
    }

    /**
     * 生成token
     * @param       string $mcrypt 秘药(配置文件的key)
     * @param       int $time 此刻,时间戳
     *
     * return       string                          加密后Token字符串
     */
    public function tokenGenerator($mcrypt, $time)
    {
        return md5(crypt(md5($this->config[$mcrypt]), substr(md5($time), 0, 10)));
    }

    /**
     * 验证token
     *
     * @param       string $token 远程传来的token
     * @param       string $mcrypt 秘药(配置文件的key)
     * @param       string $time 远程传来的,时间戳
     * @param       string $term 此token的时效性(单位为秒,如果不赋值,则不验证时效性)
     *
     * return       array                           验证结果
     */
    public function tokenVerification($token, $mcrypt, $time, $term = null)
    {
        //1. 验证时效性
        if (!empty($term)) {
            if (($time + $term) < time()) return ["status" => 411, "info" => "此token已失效", "data" => null];
        }

        //2. 验证token是否正确
        if ($this->tokenGenerator($mcrypt, $time) == $token) {
            return ["status" => 200, "info" => "此token验证成功", "data" => null];
        } else {
            return ["status" => 410, "info" => "此token验证失败", "data" => null];
        }
    }

    //字符串进行编码
    protected function StrCode($string, $action = 'ENCODE', $key)
    {
        $string .= "";
        $action != 'ENCODE' && $string = base64_decode($string);
        $code = '';
        $key = substr(md5($key), 4, 32);

        $keylen = strlen($key);
        $strlen = strlen($string);

        for ($i = 0; $i < $strlen; $i++) {
            $k = $i % $keylen;
            $code .= $string[$i] ^ $key[$k];
        }

        return ($action != 'DECODE' ? base64_encode($code) : $code);
    }


    /**
     * 参数可逆的编码————
     * @param       mixed $param 需要加密参数(混合类型)
     * @param       string $mcrypt 秘药(配置文件的key)
     *
     * return       string                              加密后的参数
     */
    public function paramEncode($param, $mcrypt)
    {
        //1. 序列化参数
        $paramstr = json_encode($param);
        //2. 加密输出
        return $this->StrCode($paramstr, "ENCODE", $this->config[$mcrypt]);
    }

    /**
     * 参数可逆的解码
     * @param       mixed $param 需要解密参数(混合类型)
     * @param       string $mcrypt 秘药(配置文件的key)
     *
     * return        string/array                       解密后的参数
     */
    public function paramDecode($encrytParam, $mcrypt)
    {
        // 去掉过长参数中间换行符
        $encrytParam = preg_replace('/\s/', '+', $encrytParam);

        //1. 进行揭秘
        $paramstr = $this->StrCode($encrytParam, "DECODE", $this->config[$mcrypt]);
        //2. json解码输出
        return json_decode($paramstr, true);
    }


    /**
     * 签名的生成器
     * @param       mixed $param 需要签名的参数
     * @param       String $mcrypt 加密的私钥
     * @param       string $time 时间戳
     *
     * return       string      签名
     */
    public function signGenerator($param, $mcrypt, $time)
    {
        //1. 序列化参数
        $paramstr = json_encode($param);

        //2 加密
        return md5(crypt(md5($paramstr), substr(md5($this->config[$mcrypt].$time), 0, 10)));

    }

    /**
     * 签名的解密器
     * @param       string $mcryp 签名的字符串
     * @param       mixed $param 需要签名的参数
     * @param       String $mcrypt 加密的私钥
     * @param       string $time 时间戳
     * @param       string $term 此token的时效性(单位为秒,如果不赋值,则不验证时效性)
     *
     * return       array       返回验证结果
     */
    public function signVerification($encrypt, $param, $mcrypt, $time, $term = null)
    {
        // 去掉过长参数中间换行符
        $param = preg_replace('/\s/', '+', $param);

        //1. 验证时效性
        if (!empty($term)) {
            if (($time + $term) < time()) return ["status" => 421, "info" => "此signa已失效", "data" => null];
        }

        //2. 验证token是否正确
        if ($this->signGenerator($param, $mcrypt, $time) == $encrypt) {
            return ["status" => 200, "info" => "此signa验证成功", "data" => null];
        } else {
            return ["status" => 420, "info" => "此signa验证失败", "data" => null];
        }
    }

    /**
     *    通过参数,时间,和秘钥生成-------加密参数,签名,和时间 的数组
     * @param       mixed           $param      需要加密的参数
     * @param       int             $time       时间戳
     * @param       string          $mcrypt     秘钥的键
     *
     *
     * return       array                       返回数组(加密参数,签名,和时间)
     */
    public function ParamAndSignToEncode($param, $time, $mcrypt){
        $param = $this->paramEncode($param,$mcrypt);
        $input['param'] = $param;
        $input['sign'] = $this->signGenerator($param,$mcrypt,$time);
        $input['time'] = $time;
        return $input;
    }

    /**
     *    解密加密参数,签名和时间
     * @param       string          $param      加密的参数
     * @param       string          $sign       签名
     * @param       int             $time       加密的参数时的时间戳
     * @param       string          $mcrypt     秘钥的键
     * @param       array           $checkKey   过滤参数中的key
     * @param       int             $term       签名的过期时间
     *
     *
     * return       array                       返回数组(状态码status, 状态信息info, 结果数据data)
     */
    public function ParamAndSignToDecode($param, $sign, $time, $mcrypt, $checkKey = array() ,$term = null){
        //1. 首先验证sign
        $sign_result = $this->signVerification($sign, $param, $mcrypt,$time, $term);
        if ($sign_result['status'] != 200){
            return $sign_result;
        }
        //2. 解密加密参数
        $result = $this->paramDecode($param, $mcrypt);

        //3. 判断解密的结果
        if(is_null($result))  return ['status' => 400, 'info' => '系统错误:签名通过,但是参数加密后,无法解密','data' => null];

        //4. 验证是否所需要的字段存在
        foreach ($checkKey as $k){
            if(!array_key_exists($k, $result)) return ['status' => 401, 'info' => '参数key为:'.$k.',不包含在内','data' => null];
        }

        return ['status' => 200, 'info' => '参数解密通过','data' => $result];
    }

    public function test()
    {
        echo 1;
    }
}
