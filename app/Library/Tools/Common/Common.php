<?php

namespace App\Library\Tools\Common;

use Ramsey\Uuid\Uuid;
use App\Library\Tools\Page\CustomPage;

class Common
{

    /**
     * CURLf方法
     * @param $url
     * @param bool $param
     * @param int $ispost
     * @param int $https
     * @return bool|mixed
     */
    public static function curl($url, $param = false, $ispost = 0, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }

        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($param) {
                if (is_array($param)) {
                    $param = http_build_query($param);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $param);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            return false;
        }
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    /**
     * 加密算法
     * @author jiaxiaofei
     * @param string $pwd
     */
    public static function md($pwd)
    {
        return md5(md5($pwd . 'zuzhanggui') . 'zuzhanggui');

    }

    /**
     * 返回uuid
     * @return string
     */
    public static function getUuid()
    {
        $uuid = Uuid::uuid1();
        return $uuid->getHex();
    }

    /**
     *  获取本月第一天和最后一天
     * @param $date
     * @return array
     */
    public static function getMonth($date)
    {
        $firstday = date("Y-m-01", strtotime($date));
        $lastday = date("Y-m-d", strtotime("$firstday +1 month -1 day"));
        return array($firstday, $lastday);
    }


    /**
     *  获取上个月第一天和最后一天
     * @param $date
     * @return array
     */
    public static function getlastMonthDays($date)
    {
        $timestamp = strtotime($date);
        $firstday = date('Y-m-01', strtotime(date('Y', $timestamp) . '-' . (date('m', $timestamp) - 1) . '-01'));
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        return array($firstday, $lastday);
    }


    /**
     *  获取下个月第一天和最后一天
     * @param $date
     * @return array
     */
    public static function getNextMonthDays($date)
    {
        $timestamp = strtotime($date);
        $arr = getdate($timestamp);
        if ($arr['mon'] == 12) {
            $year = $arr['year'] + 1;
            $month = $arr['mon'] - 11;
            $firstday = $year . '-0' . $month . '-01';
            $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        } else {
            $firstday = date('Y-m-01', strtotime(date('Y', $timestamp) . '-' . (date('m', $timestamp) + 1) . '-01'));
            $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        }
        return array($firstday, $lastday);
    }

    /**
     * 获取分页的URL
     * @param  array $pageData
     * @param  array $result
     * @param  string $url
     * @param  array $param
     * @return mixed(array)
     * @author 郭鹏超
     */
    public static function getPageUrl($nowPage, $totalPage, $url, $param = '')
    {
        if (!empty($nowPage) && !empty($totalPage) && !empty($url)) {
            $pages = CustomPage::getSelfPageView($nowPage, $totalPage, url($url), $param);
            if ($pages) return $pages;
        }
        return false;
    }

    /**
     * 产生cookie
     * @return string
     * @author
     */
    public static function generateCookie($key)
    {
        if (empty($key)) return false;
        $value = md5(REGISTER_SIGNATURE . $key);
        return cookie($key, $value, COOKIE_LIFETIME);
    }

    /**
     * 用户注册生成随机串
     * @param  int 生成长度
     * @return string 生成的字条串
     */
    public static function random($length)
    {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * 无限分类
     *
     * @param   array $data 待分类的数据
     * @param   int /string   $role_id        要找的子节点id
     * @param   string $parentNode 父节点名字
     * @param   string $childNodes 子节点名字
     * @param   string $name 工作名称
     * @param   int $lev 节点等级
     * @return array  数组
     * @author sunhanghao
     */
    public static function getSubTree($data, $parentNode, $childNode, $role_id = 0, $lev = 0, $parentsName = '最上级', $name = 'name')
    {
        static $childNodes = array();

        foreach ($data as $key => $value) {
            if ($value[$parentNode] == $role_id) {
                $value['parentsName'] = $parentsName;
                $parentsName = $value[$name];
                $value['lev'] = $lev;
                $childNodes[] = $value;
                self::getSubTree($data, $parentNode, $childNode, $value[$childNode], $lev + 1, $parentsName, $name);
            }
        }
        return $childNodes;
    }

    /**
     * 无限分类
     *
     * @param   array $arr 待分类的数据
     * @param   int /string   $departmen_id        要找的子节点id
     * @param   int $level 节点等级
     * @return array  数组
     * @author sunhanghao
     */
    public static function getTree($arr, $id = 0, $lev = 0)
    {
        // 获取子孙树
        if (empty($arr)) {
            return false;
        }
        $tree = [];
        foreach ($arr as $v) {
            if ($v->pid == $id) {
                $v->level = $lev;
                $tree[] = $v;
                $tree = array_merge($tree, self::getTree($arr, $v->id, $lev + 1));
            }
        }
        return $tree;
    }

    /**
     * 为redis缓存添加GLOBALS
     *
     * @return array
     * @author sunchanghao
     */
    public static function addGLOBALS($param)
    {
        // 引入redis缓存名
        foreach($param as $key =>&$value){
            if(is_array($value)){
                //无限遍历
                $value=  self::addGLOBALS($value);
            }else{
                //添加$GLOBALS['database']
                $value= $GLOBALS['database'].$value;

            }
        }
        return $param;
    }

}
