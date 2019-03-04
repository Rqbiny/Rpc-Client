<?php
/**
 *   定义全局函数
 *
 * CustomerModel: Redscarf
 * Last Modify: 17/6/19
 * Final Modifier: Redscarf
 */


/**
 *   响应方法
 *
 * @code 响应码
 * @data 响应内容
 * @type 响应类型（选填 1加密 0不加密(默认是0)）
 *   return {"serverTime":"1470000000", 'ServerNo':404, 'ResultMsg':"服务未找到",'ResultData':[]}
 */
function responseData($serverCode = 200, $serverMsg = "操作成功", $serverData = [], $type = 0, $param = '')
{
    // //从配置文件里面找出对应返回的信息
    // $serverMsg = $this->config['code'][$serverCode]?$this->config['code'][$serverCode]:"未知错误！";
    // //匹配传入的加密状态
    // switch ($type) {
    //     //使用加密类Secret加密
    //     case '1':
    //         $serverData = $this->encode($serverData);
    //         break;
    //     //默认状态是不加密
    //     default:
    //         $serverData = $serverData;
    //         break;
    // }
    // //如果返回信息里面有变量则替换为传入值
    // $serverMsg = str_replace('{{$param}}', $param, $serverMsg);
    //返回响应数据
    return response()->json(
        [
            'serverTime' => time(),
            'ServerNo' => $serverCode,
            'ResultMsg' => $serverMsg,
            'ResultData' => $serverData
        ]
    );
}

/**
 * 检测是否是手机浏览
 * @param
 * @return bool
 */
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return TRUE;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? TRUE : FALSE;// 找不到为flase,否则为TRUE
    }
    // 判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'mobile', 'nokia', 'sony', 'ericsson', 'mot',
            'samsung', 'htc', 'sgh', 'lg', 'sharp',
            'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo',
            'iphone', 'ipod', 'blackberry', 'meizu', 'android',
            'netfront', 'symbian', 'ucweb', 'windowsce', 'palm',
            'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc',
            'midp', 'wap'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return TRUE;
        }
    }
    if (isset ($_SERVER['HTTP_ACCEPT'])) { // 协议法，因为有可能不准确，放到最后判断
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return TRUE;
        }
    }
    return FALSE;
}
