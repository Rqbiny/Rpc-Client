<?php
/**
 * Created by PhpStorm.
 * User: renqingbin
 * Date: 2018/1/23
 * Time: 上午11:31
 */
namespace App\Library\Tools\QiNiu;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use App\Library\Tools\Common\Common;

class QiNiu
{
    protected $accessKey;
    protected $secretKey;
    protected $baseBucket;
    protected $cacheBucket;

    public function __construct()
    {
        $this->accessKey = env('QINIU_ACCESSKEY');
        $this->secretKey = env('QINIU_SECRETKEY');
        $this->bucket = env('QINIU_BUCKET');
        $this->cacheBucket = env('QINIU_CACHE_BUCKET');
    }

    /**
     * 生成token
     *
     * @param
     * @return string
     * @author renqingbin
     */
    protected function Token($bucket, $key = null, $expires = 3600, $policy = null, $strictPolicy = true)
    {
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = $this->accessKey;
        $secretKey = $this->secretKey;
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 要上传的空间
        $bucket = $bucket;
        // 生成上传 Token
        return $auth->uploadToken($bucket, $key, $expires, $policy, $strictPolicy);
    }

    /**
     *  上传文件缓存内容
     *
     * @param $fileName 上传到七牛的文件名
     * @param $content 上传内容
     * @return mixed
     * @author renqingbin
     */
    public function uploadContent($fileName, $content)
    {
        // 获取上次token
        $token = $this->Token($this->cacheBucket, $fileName);
        $uploadMgr = new UploadManager();
        // 开始上次
        list($ret, $err) = $uploadMgr->put($token, $fileName, $content);
        // 获取上传结果
        if ($err !== null) {
            app('log')->error("文件缓存".$fileName.'上传错误，错误状态'.$err->message());
            return false;
        } else {
            return true;
        }
    }

    /**
     * 七牛云文件上传
     * $name 业务名字
     * @param $name
     * @return mixed
     * @author sunchanghao
     */
    public function uploadFile($fileName,$filePath)
    {
        // 获取上次token
        $token = $this->Token($this->bucket, $fileName);
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret,$err) = $uploadMgr->putFile($token, $fileName, $filePath);
        if($err === null){
            return $ret['key'];
        }else{
            app('log')->error("文件".$fileName.'上传错误，错误状态'.$err->message());
            return false;
        }
    }

    /**
     *  获取七牛的缓存文件
     *
     * @param  要获取的文件名
     * @author renqingbin
     */
    public function getContent($fileName)
    {
        // 从env配置获取七牛的域名
        $qiniu_url = env('QINIU_URL');
        $url = $qiniu_url."/".$fileName;
        // 获取文件信息
        $resultJson = Common::curl($url);
        // 判断请求是否成功
        if($resultJson){
            $resultArr = json_decode($resultJson, true);
            // 判断是否七牛出现错误
            if(isset($resultArr['error'])){
                app('log')->error("文件缓存".$url.'出现错误，错误状态'.$resultArr['error']);
                return [
                    'status' => false,
                    'info' => $resultArr['error']
                ];
            }else{
                return [
                    'status' => true,
                    'data' => $resultArr
                ];
            }
        }else{
            return [
                'status' => false,
                'info' => '文件缓存获取失败'
            ];
        }
    }
}