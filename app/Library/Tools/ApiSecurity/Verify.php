<?php
namespace App\Library\ApiSecurity\Tools;

//use App\RedisStore\AppRedisStore as AppRedis;
use App\Library\Tools\Redis\RedisTool;
use App\Model\User\UserLoginModel;

class Verify
{
    // 静态变量
    private static $redisIndex = 1;
    //定义redis工具
    protected static $redisTools;
    protected static $userLoginModel;

    /**
     * 单例注入
     *
     * Verify constructor.
     * @param AppRedis $appRedis
     * @author　郭鹏超
     */
    public function __construct(
                                UserLoginModel $userLoginModel,
                                RedisTool $redisTools
    )
    {
        //加载缓存工具
        self::$redisTools = $redisTools;
        self::$userLoginModel = $userLoginModel;
    }

    /**
     * 通用接口
     * @param $request
     * @return bool|string
     */
    public function common($request)
    {
        if($request->all()){
            $data = $request->all();
            $ckTime = $this->checkTime($data['time']);
            if(!$ckTime) return 'SN002';
            // 判断只有在检测接口路由时才会检测版本
            if($request->path()=='checkVersion'){
                return "SN200";
            }
            if(!isset($data['guid'])) return 'SN004';
            if(!isset($data['version'])) return 'SN003';
            // 根据版本设计不同的验证
            switch ($data['version']){
                case '1.0':
                    $temp = $this->checkCommon_v1($request);
                    break;
                default:
                    $temp = $this->checkCommon_v1($request);
                    break;
            }
            if($temp){
                return "SN200";
            }
            return "SN005";
        }
        return false;
    }

    /**
     * 非通用接口
     * @param $request
     * @return bool|string
     */
    public function proprietary($request)
    {
        if($request->all()){
            $data = $request->all();
            $ckTime = $this->checkTime($data['time']);
            if(!$ckTime) return 'SN002';
            if(!isset($data['guid'])) return "SN004";
            if(!isset($data['version'])) return 'SN003';
            // 根据版本设计不同的验证
            switch ($data['version']){
                case '1.0':
                    $temp = $this->checkProprietary_v1($request);
                    break;
                default:
                    $temp = $this->checkProprietary_v1($request);
                    break;
            }
            if($temp){
                // return "SN200";
                switch ($temp){
                    case 'SN007':
                        return 'SN007';
                        break;
                    case 'SN006':
                        return 'SN008';
                        break;
                    case 'SN009':
                        return 'SN009';
                        break;
                    default:
                        return 'SN200';
                        break;
                }
            }
                return "SN005";
        }
        return false;
    }

    /**
     * 时间验证
     * @param $time
     * @return bool|string
     */
    public function checkTime($time)
    {
        $Time_difference = abs(time()-$time);
        if($Time_difference>30){
            return false;
        }
        return true;
    }

    /**
     * 通用接口验证
     * @param $request
     * @return bool
     */
    private function checkCommon_v1($request)
    {
        $data = $request->all();
        $path = $request->path();
        $time = $data['time'];
        $guid = $data['guid'];
        $param = $data['param'];
        $cryptToken = "hengda2017";
        $num = $path.$time.$guid.$param.$cryptToken;

        $signature = md5($path.$time.$guid.$param.$cryptToken);
        if($signature!=$data['signature']){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 非通用接口验证
     * @param $request
     * @return bool
     */
    private function checkProprietary_v1($request)
    {
        $data = $request->all();
        $path = $request->path();
        $param = $data['param'];
        $guid = $data['guid'];
        $signature = $data['signature'];
        $time = $data['time'];
        $user = $this->user($guid);
        if(!$user) return 'SN007';  // 用户不存在
        // $tokenTime = $user['token_time'];
        // if($time > $tokenTime){
        //     return 'SN009';     // token超时 重新登录
        // }
        $token = $user['token'];
        $hashs = [
            [2, 3, 1, 17, 22, 28],
            [0, 8, 19, 23, 30, 31],
            [9, 15, 31, 1, 5, 7],
            [11, 21, 31, 10, 12, 16],
            [30, 1, 12, 18, 25, 28],
            [8, 14, 17, 27, 1, 4],
            [2, 8, 13, 19, 20, 24],
            [5, 16, 20, 29, 18, 22]
        ];
        $strs = $token['2'];
        $strs.= $token['5'];
        $strs.= $token['8'];
        $code = hexdec($strs);
        $str1 = $code%8;
        $arr =$hashs["$str1"];
        $m = null;
        foreach($arr as $v){
            $m.= substr($token,$v,1);
        }
        $str = md5($path.$time.$guid.$param.$m);
        // \Log::info($str);
        if($signature == $str){
            return 'SN200';
        }else{
            return false;
        }
    }

    /**
     * 路由判断方法
     * @param $path
     * @return bool
     */
    public function inPath($path)
    {
        $paths = ['user/smsauth', 'user/register', 'user/login', 'user/retrievePwd', 'home/homeListBanner', 'home/homeSearch'];
        return in_array($path, $paths);
    }

    /**
     * 参数拼装
     * @param $info
     * @return response
     */
    public function potting($info)
    {
        if($info->getStatusCode()==200){
            return response()->json(['serverTime'=>time(),'ServerNo'=>'SN200','ResultData'=>$info->original]);
        }else{
            return response()->json(['serverTime'=>time(),'ServerNo'=>'SN'.$info->getStatusCode(),'ResultData'=>$info->original]);
        }
    }

    /**
     * 获取App用户资料
     * @param $guid
     * @return array()
     */
    public function user($guid)
    {
        $hashKey='APP:USER:MESSAGE:'. $guid;
        //取出该用户所有的缓存
        $userInfo = self::$redisTools->hashGetAll($hashKey);
        //判断是否有改用的缓存
        if (empty($userInfo)) {
            $userInfo = self::$userLoginModel->getSingleData(['user_id' => $guid]);
            //判断是否取出数据
            if (empty($userInfo)) {
                return [];
            } else {
                //将数据转成数组
                $userInfo = collect($userInfo)->toArray();
                self::$redisTools->hashMSet($hashKey, $userInfo);
                return $userInfo;
            }
        }
        return $userInfo;
    }
}
