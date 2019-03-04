<?php

namespace App\Http\Middleware;

use App\Library\Tools\ApiSecurity\Verify;
use Closure;

class ApiMiddleware
{
    private static $verify;

    public function __construct(Verify $verify)
    {
        self::$verify = $verify;
    }

    private function verify($request)
{
    $data = $request->all();
    if($data['guid'] == '0'){
        return  self::$verify->common($request);
    }else{
        return self::$verify->proprietary($request);
    }
    return false;
}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // return $next($request);
        $time = time();
        switch($this->verify($request))
        {
            case 'SN200':
                $temp =  $next($request);
                //后置中间件
                // return self::$verify->potting($temp);
                // 封装
                return $temp;
                break;
            case 'SN001':
                return response()->json(['serverTime'=>$time,'ServerNo'=>'1','ResultData'=>'Server internal error!']);
                break;
            case 'SN002':
                return response()->json(['serverTime'=>$time,'ServerNo'=>'2','ResultData'=>'Request timeout!']);
                break;
            case 'SN003':
                return response()->json(['serverTime'=>$time,'ServerNo'=>'3','ResultData'=>'Version number exception!']);
                break;
            case 'SN004':
                return response()->json(['serverTime'=>$time,'ServerNo'=>'4','ResultData'=>'Global user ID can not be null!']);
                break;
            case 'SN005':
                return response()->json(['serverTime'=>$time,'ServerNo'=>'5','ResultData'=>'Signature error!']);
                break;
            case 'SN007':
                return response()->json(['serverTime'=>$time,'ServerNo'=>'7','ResultData'=>'user not!']);
                break;
            case 'SN008':
                return response()->json(['serverTime'=>$time,'ServerNo'=>'8','ResultData'=>'Other devices login!']);
                break;
            case 'SN009':
                return response()->json(['serverTime'=>$time,'ServerNo'=>'9','ResultData'=>'token time out!']);
                break;
            default:
                return response()->json(['serverTime'=>$time,'ServerNo'=>'6','ResultData'=>'No access!']);
        }
    }
}
