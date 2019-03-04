<?php
/**
 * Created by PhpStorm.
 * CustomerModel: xdl
 * Date: 16/8/9
 * Time: 下午8:44
 */
namespace App\Library\SystemSecurity\source;


use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;



class SecurityClass
{
    private $config;

    private $yzmName;                   //session中的 验证码的名称

    private $cookieGuardConfig;         //使用cookie进行拦截,非正常访问的用户

    private $IPInterceptConfig;         //ip拦截的配置信息

    public function __construct()
    {
        //引入配置文件
        $this->config =  include __DIR__.'/../SecurityConfig.php';

        /**
         *    设置配置文件参数引入
         */
        // 1. 验证的配置
        $this->yzmName = empty($this->config['yzmName']) ? 'yzmCode' : $this->config['yzmName'];

        /**
         *    2. ip 防火墙的配置
         */
        $this->IPInterceptConfig = $this->config['IPIntercept_default'];
    }

    /**
     *    验证码的生成器
     */
    public function yzmGenerator($width = 100 , $height = 40, $filename = null){
        $phrase= new PhraseBuilder;
        $code=$phrase->build(4);
        //生成验证码图片的Builder对象，配置相应属性
        $builder = new CaptchaBuilder($code,$phrase);
        //设置背景颜色
        $builder->setBackgroundColor(220,220,220);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        //可以设置图片宽高及字体
        $builder->build($width , $height, null);
       // $builder->build($width = 100, $height = 40, $font = null);
        //获取验证码的内容
        $phrase = $builder->getPhrase();

        //把内容存入session
        \Session::flash($this->yzmName, $phrase);
        //生成图片
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        if(empty($filename)){
            $builder->output();
        }else{
            $builder->save($filename);
        }
    }

    /**
     *    验证码验证,根据cookie来验证
     */
    public function yzmVerification($zymcode){
        $sysCode = \Session::get($this->yzmName);
        if($zymcode == null) return false;
        return $sysCode == $zymcode ? true : false;
    }
    
    /**
     *    批量生成页面静态的验证码图片
     * @param       $width
     * @param       $height
     * @param       $address        验证码的目录
     * @param       $num            批量生成验证码的个数
     */
    public function zymStaticImages($width , $height, $address, $num = 1){
        //1. 首先获取配置文件信息
        if(empty($address)){
            $address = $this->config['cookieGuardConfig']['storageAddress'];
        }
        if(empty($address)) return  ['status'=>false, 'info'=> '请配置存放地址'];
        $address = rtrim($address,'/');
        //2. 存放图片
        for ($i = 0; $i < $num; $i++ ){
            $this->yzmGenerator($width,$height, $address.'/'.$i.'.jpeg');
        }
        return ['status'=>true, 'info'=> '验证码静态图片生成完毕'];
    }

    /**
     *    cookie拦截生成器
     * @param   $ConfigKey      为配置文件中的cookiename
     */
    public function cookieGenerator($ConfigKey = null){
        //1. 首先获取配置文件信息
        if(!empty($ConfigKey)){
            if(empty($this->config['cookieGuardConfig']['cookiename'][$ConfigKey])) return ['status'=>false, 'info'=> '找不到配置文件'];
            $this->cookieGuardConfig = $this->config['cookieGuardConfig']['cookiename'][$ConfigKey];
        }else{
            $this->cookieGuardConfig = $this->config['cookieGuardConfig']['cookiename']['default'];
        }
        //2. 设置cookie
        setcookie($this->cookieGuardConfig['key'],$this->cookieGuardConfig['value'],time()+9999999,'/');

        return  ['status'=>true, 'info'=> '设置cookie成功'];
    }

    /**
     *    cookie拦截验证
     * @param   $ConfigKey      为配置文件中的cookiename
     */
    public function cookieVerification($ConfigKey = null){
        //1. 首先获取配置文件信息
        if(!empty($ConfigKey)){
            if(empty($this->config['cookieGuardConfig']['cookiename'][$ConfigKey])) return ['status'=>false, 'info'=> '找不到配置文件'];
            $this->cookieGuardConfig = $this->config['cookieGuardConfig']['cookiename'][$ConfigKey];
        }else{
            $this->cookieGuardConfig = $this->config['cookieGuardConfig']['cookiename']['default'];
        }
        //dd($this->cookieGuardConfig);
        if(!empty($_COOKIE[$this->cookieGuardConfig['key']]) && $_COOKIE[$this->cookieGuardConfig['key']] == $this->cookieGuardConfig['value']){
            return ['status'=>true, 'info'=> 'cookie匹配正确'];
        }
        return ['status'=>false, 'info'=> 'cookie匹配失败'];
    }


    /**
     *  第一部分:  1.1 入口
     *          IP防护墙的监控操作方法
     *
     *           IP防护墙, 当IP在单位时间类多次访问,则让其进入黑名单
     *
     *  param       confignName         自定义配置文件下中
     */
    public function IPIntercept($configName = null){
       // \MyRedis::flushall();

        //1. 判断用户是否使用自定义配置文件信息,过
        $config_result = $this->dealConfigData($configName);
        if($config_result['status']  == 500 ) return $config_result;         //判断是否成功获取配置文件信息
        //dd($config_result)                否则返回为['status' => 200, 'info' => '参数配置成功']

        //2. 获取用户远程ip
        $remoteip = $_SERVER['REMOTE_ADDR'];

        //3. 首先对黑白ip进行处理
        $IPList_result = $this->dealIPlist($remoteip);
        if($IPList_result['status'] == 501) return ['status' => 501, 'info' => '此ip已经在黑名单中'];
        if($IPList_result['status'] == 201) return ['status' => 201, 'info' => '此ip已经在白名单中'];
        //dd($IPList_result);              否则返回为['status' => 202 , 'info' => '此ip属于正常访问ip']

        //4. 监听正常ip的访问, 如过状态码不大于500 ,则返回结果并告知处理信息
        $listen_result = $this->listenIP($remoteip);
        if($listen_result['status'] == 203) return ['status' => 203 , 'info' => '此ip第一次访问此接口'];
        if($listen_result['status'] == 204) return ['status' => 204 , 'info' => '此ip已经连续访问,但是还没有到警告阀值'];
        if($listen_result['status'] == 205) return ['status' => 205 , 'info' =>  str_replace('#',round($this->IPInterceptConfig['expireTime']/60),$this->IPInterceptConfig['warnningLanag'])];
        //dd($listen_result);                 否则返回['status' => 502 , 'info' =>  $this->IPInterceptConfig['dieLanguage']];

        //5. 处理违法规约的ip,拉入黑名单,并且添加到黑名单中.
        if(!empty($this->IPInterceptConfig['IPIntercepRecordToDatabase'])){
            $insertinfo = ['ip'=>$remoteip,
                'describle'=>'此ip于'.date('Y-m-d H:i:m',$listen_result['data']['startTime']).'开始连续访问,在'.date('Y-m-d H:i:m').'攻击了'.$listen_result['data']['num'].'次,被系统拉入黑名单',
                'status' => 1,
                'addtime' =>time()];
            $addToBlackList_result = $this->addIpToMysqlAndRedis($insertinfo);               //添加数据到redis和mysql中
        }else{
            $addToBlackList_result = true;
        }

        if($addToBlackList_result){
            return  ['status' => 502 , 'info' =>  $this->IPInterceptConfig['dieLanguage']];
        }else{
            return  ['status' => 503 , 'info' =>  '系统错误,此ip已经违规,但是未指明添加是黑名单还是白名单'];
        }
    }
    /**
     *    1.2 处理配置文件基本信息
     *
     *      注释: 用户只需要配置基本信息即可, 黑白名单的配置一般统统使用默认配置信息即可,   但也可以在自定义的配置文件做响应的修改
     */
    protected function dealConfigData($configName){
        if(!empty($configName)){
            if (empty($this->config[$configName])) return ['status' => 500, 'info' => '配置文件信息找不到,请检测配置文件的key'];
            foreach($this->IPInterceptConfig as $k => $v){
                if(!empty($this->config[$configName][$k])){
                    $this->IPInterceptConfig[$k] = $this->config[$configName][$k];
                }
            }
        }
        return ['status' => 200, 'info' => '参数配置成功'];
    }

    /**
     *   第一部分: 1.3  处理黑白名单
     */
    protected function dealIPlist($remoteip){
        //1. 首先判断黑白名单缓存是否存在 , 如果不存在,则检表加载黑白名单ip数据到缓存
        if(!\Redis::exists($this->IPInterceptConfig['IPInterceptBlackList'])){
            $IPList = \DB::table($this->IPInterceptConfig['tableNameOfDatabase'])->get(['ip','status']);
            foreach ($IPList as $ip){
                if($ip->status == 1){
                    \Redis::rpush($this->IPInterceptConfig['IPInterceptBlackList'], $ip->ip);           //加载黑名单
                }else{
                    \Redis::rpush($this->IPInterceptConfig['IPInterceptWhiteList'], $ip->ip);           //加载白名单
                }
            }
            \Redis::lpush($this->IPInterceptConfig['IPInterceptBlackList'],'');                         //推一个空值,防治数据库无数据,每次检测数据库
            \Redis::expire($this->IPInterceptConfig['IPInterceptBlackList'],$this->IPInterceptConfig['IPInterceptListTime']);                        //设置过期时间
            \Redis::expire($this->IPInterceptConfig['IPInterceptWhiteList'],$this->IPInterceptConfig['IPInterceptListTime']);                       //设置过期时间
        }

        //2. 判断是否在白名单中
        $whitelist = \Redis::lrange($this->IPInterceptConfig['IPInterceptWhiteList'],0,-1);
        $whitelist = array_merge($whitelist,$this->IPInterceptConfig['whiteList']);
        if(in_array($remoteip,$whitelist))  return ['status' => 201, 'info' => '此ip已经在白名单中'];

        //3. 判断是否在黑名单中
        $blacklist = \Redis::lrange($this->IPInterceptConfig['IPInterceptBlackList'],0,-1);
        if(in_array($remoteip,$blacklist))  return ['status' => 501, 'info' => '此ip已经在黑名单中'];


        //4. 正常ip
        return ['status' => 202 , 'info' => '此ip属于正常访问ip'];
    }

    /**
     *  第一部分: 1.4  监听正常访问的ip
     */
    protected function listenIP($remoteip){
        //1. 获取监听的ip详细信息
        $ipInfo = \Redis::hgetall($this->IPInterceptConfig['IPInterceptExistence'].$remoteip);

        //2. 如果此ip是第一次进入,则初始化基本信息
        if(empty($ipInfo)){
            $initData = ['startTime' => time(), 'num' => 1];
            \Redis::hmset($this->IPInterceptConfig['IPInterceptExistence'].$remoteip, $initData);
            \Redis::expire($this->IPInterceptConfig['IPInterceptExistence'].$remoteip, $this->IPInterceptConfig['expireTime']);
            return ['status' => 203 , 'info' => '此ip第一次访问此接口'];
        }

        //3. 如果ip已经被访问过,判断此ip是否在警告中值之下,并且重置生存时间
        if($ipInfo['num'] <= $this->IPInterceptConfig['warnningTime']){
            $updateData = ['startTime' => $ipInfo['startTime'], 'num' => $ipInfo['num']+1 ];
            \Redis::hmset($this->IPInterceptConfig['IPInterceptExistence'].$remoteip, $updateData);
            \Redis::expire($this->IPInterceptConfig['IPInterceptExistence'].$remoteip, $this->IPInterceptConfig['expireTime']);
            return ['status' => 204 , 'info' => '此ip已经连续访问,但是还没有到警告阀值'];
        }

        //4. 如果ip已经被访问过,判断此ip是否在警告值之上,但是在拉黑之下,并重置生存时间
        if($ipInfo['num'] <= $this->IPInterceptConfig['dieTime']){
            $updateData = ['startTime' => $ipInfo['startTime'], 'num' => $ipInfo['num']+1 ];
            \Redis::hmset($this->IPInterceptConfig['IPInterceptExistence'].$remoteip, $updateData);
            \Redis::expire($this->IPInterceptConfig['IPInterceptExistence'].$remoteip, $this->IPInterceptConfig['expireTime']);
            return ['status' => 205 , 'info' =>  str_replace('#',round($this->IPInterceptConfig['expireTime']/60),$this->IPInterceptConfig['warnningLanag'])];
        }

        //5. 当连续访问的次数到达dieTime上线时, 此ip即将被拉黑
        if($ipInfo['num'] > $this->IPInterceptConfig['dieTime']){
            return ['status' => 502 , 'info' =>  $this->IPInterceptConfig['dieLanguage'],'data' => $ipInfo];
        }
    }
    /**
     *    第二部分:   IP防护墙:  生成黑名单数据库表
     *
     * @param   $configName     配置文件的下标名
     */
    public function createTable($configName = null)
    {
        //1. 判断用户是否使用自定义配置文件
        if(!empty($configName)){
            if (empty($this->config[$configName])) return ['status' => 500, 'info' => '配置文件信息找不到'];
            $this->IPInterceptConfig = $this->config[$configName];
        }

        // 2. 连接数据库
        $host     = env('DB_HOST', 'localhost');
        $database = env('DB_DATABASE', 'forge');
        $username = env('DB_USERNAME', 'forge');
        $password = env('DB_PASSWORD', '');
        $charset  = 'utf8';

        $mysqli = new \mysqli($host, $username, $password ,$database);
        $mysqli -> set_charset($charset);

        // 3. sql语句
        $sql = 'create table if not exists '.$this->IPInterceptConfig['tableNameOfDatabase'].'(
                id int not null primary key auto_increment,
                ip varchar(30) not null,
                describle VARCHAR (255) not null,
                `status` tinyint not null COMMENT "1表示黑名单,2表示白名单",
                `addtime` int(10) not null
              ) engine = MyISAM default CHARSET=utf8 ';

        // 4. 执行语句,并且返回结果
        $result = $mysqli->query($sql);

        if ($result) return ['status' => 200, 'info' => '建表成功'];
        return ['status' => 500, 'info' => '建表失败'];
    }

    /**
     *    第三部分:   IP防护墙:  手动添加 <<Ip文件>> 到数据库
     *
     *      格式为:
     *              1. 必须是txt文件
     *              2. 每行必须只有一个ip,  (每个ip后面有\n)不然就报错
     *
     * @param   $filename           加载文件名
     * @param   $blackOrwhite       默认为加载黑名单ip      如果需要加载白名单请赋值为2
     * @param   $configName         配置文件的下标
     */
    public function addGuardIpToTable($filename = null,$blackOrwhite = 1, $configName = null)
    {
        //1. 判断用户是否使用自定义配置文件
        if(!empty($configName)){
            if (empty($this->config[$configName])) return ['status' => 500, 'info' => '配置文件信息找不到'];
            $this->IPInterceptConfig = $this->config[$configName];
        }

        //2. 判断是否有实参数,  否者取配置文件,  如果都没有就返回
        if(empty($filename)){
            if(empty($this->IPInterceptConfig['addGuardIPofFIle'])) return ['status' => 500, 'info' => '请指定文件路劲'];
            $filename = $this->IPInterceptConfig['addGuardIPofFIle'];
        }

        //3. 判断文件是否存在, 并且必须是txt文件
        if(!file_exists($filename)) return ['status' => 500, 'info' => '给出的文件地址不存在,请仔细检查'];
        if(strpos($filename,'txt') === false) return ['status' => 500, 'info' => '请专为txt文件,在进行执行'];

        // 4. 切割文件
        $contains = trim(file_get_contents($filename));
        $pattern = '/\d{1,3}.1\d{1,3}.\d{1,3}.\d{1,3}/';
        preg_match_all($pattern,$contains,$ips);
        $ips = $ips[0];
        if(!$ips)   return ['status' => 500, 'info' => 'sorry,正则没有匹配到任何结果'];

        //5. 插入数据库
        $blackOrwhite = $blackOrwhite == 2 ? 2 : 1;
        $num = 0;
        foreach($ips as $k => $ip){
            $insertinfo = ['ip'=>$ip, 'describle'=>'正在使用文件添加ip黑名单'.date('Y-m-d H:i:m'),'status' => $blackOrwhite, 'addtime' =>time()];
            $findinfo = ['ip'=>$ip];
            $result = \DB::table($this->IPInterceptConfig['tableNameOfDatabase'])->where($findinfo)->first();
            if($result){
                $num ++;
                continue;
            }
            $this->addIpToMysqlAndRedis($insertinfo);               //添加数据到redis和mysql中
        }
        $k++;
        return ['status' => 200, 'info' => '已经成功把文件所有ip添加到数据库黑名单','data' => "共计{$k}个ip,插入了".($k - $num)."个,从数据库检测后,过滤了{$num}个"];
    }
    /**
     *    添加到数据库和缓存中
     */
    protected function addIpToMysqlAndRedis($insertinfo)
    {

        //1. 判断是否为白黑名单,并添加到redis中
        if($insertinfo['status'] == 1){             //黑名单
            \Redis::rpush($this->IPInterceptConfig['IPInterceptBlackList'], $insertinfo['ip']);
        }else if($insertinfo['status'] == 2){       //白名单
            \Redis::rpush($this->IPInterceptConfig['IPInterceptWhiteList'], $insertinfo['ip']);
        }else{
            return  ['status' => false, 'info' => "系统错误,请指定黑白名单"];
        }
        //2. 插入数据库中
        \DB::table($this->IPInterceptConfig['tableNameOfDatabase'])->insertGetId($insertinfo);
        return  ['status' => true, 'info' => "添加redis和mysql数据库成功"];
    }




    public function test(){

        dd('aa');
    }
}
