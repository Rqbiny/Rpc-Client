<?php
namespace App\Library\Tools\Redis;


/**
 * 操作Redis的工具类
 *
 * @author  renqingbin
 * @date    20170411
 */
class RedisTool
{
    /*** List列表操作 ***/
    /**
     * list添加值到列表头部
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  int     列表的长度
     * @author  renqingbin
     * @date    20170411  Redis Lpush 命令将一个或多个值插入到列表头部(放入到表头)
     */
    public function listPushTop($keyName, $value)
    {
        return app('redis')->LPUSH($keyName, $value);
    }

    /**
     * list添加值到列表的底部
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  int     列表的长度
     * @author  renqingbin
     * @date    20170411
     */
    public function listPushBottom($keyName, $value)
    {
        return app('redis')->RPUSH($keyName, $value);
    }

    /**
     * list按照范围查询操作
     *
     * @param   $keyName
     * @param   $start
     * @param   $end
     * @return  list     包含指定区间内的元素
     * @author  renqingbin
     * @date    20170411  Redis LRANGE命令将返回存储在key列表的特定元素。偏移量开始和停止是从0开始的索引
     */
    public function listGet($keyName, $start = 0, $end = -1)
    {
        return app('redis')->LRANGE($keyName, $start, $end);
    }

    /**
     * list,获取列表长度的方法
     *
     * @param   $keyName
     * @return  int
     * @author  renqingbin
     * @date    20170413  Redis LLEN命令将返回存储在key列表的长度。
     */
    public function listLength($keyName)
    {
        return  app('redis')->LLEN($keyName);
    }

    /**
     * list，根据键的索引更新其值
     *
     * @param   $keyName
     * @param   $keyIndex
     * @param   $data
     * @return  string     成功返回ok
     * @author  renqingbin
     * @date    20170412   Redis Lset 通过索引来设置元素的值。
     */
    public function listSet($keyName, $keyIndex, $data)
    {
        return app('redis')->LSET($keyName, $keyIndex, $data);
    }

    /**
     * list， 根据键名、元素值匹配删除
     *
     * @param   $keyName
     * @param   $value
     * @param   $count
     * @author  renqingbin
     * @date    20170413  Redis Lrem 根据参数 COUNT 的值，移除列表中与参数 VALUE 相等的元素。
     */
    public function listDelKeyValue($keyName, $count = 0, $value)
    {
        return app('redis')->LREM($keyName, $count, $value);
    }

    /*** Hash操作 ***/
    /**
     * hash获取域所有值的操作
     *
     * @param   $keyName 域名
     * @return  int     列表的长度
     * @author  renqingbin
     * @date    20170411  Redis Hgetall 命令用于返回哈希表中，所有的字段和值。(取出所有缓存)
     */
    public function hashGetAll($keyName)
    {
        return app('redis')->HGETALL($keyName);
    }

    /**
     * hash获取单个值的操作
     *
     * @param   $keyName 域名
     * @param   $value  单个值
     * @return  int     列表的长度
     * @author  renqingbin
     * @date    20170411  Redis HGET命令用于获取与字段中存储的键哈希相关联的值 (获取键哈希值)
     */
    public function hashGet($keyName, $value)
    {
        return app('redis')->HGET($keyName, $value);
    }

    /**
     * redis 单个哈希设置值
     *
     * @param   $keyName
     * @param   $key hash键
     * @param   $value 要设置的值
     * @return  int
     * @author  renqingbin
     * @date    20170807
     */
    public function hashSet($keyName ,$key, $value)
    {
        return app('redis')->HSET($keyName ,$key, $value);
    }

    /**
     * hash获取设置一个哈希表里多个域与值
     *
     * @param   $keyName 域名
     * @param   $value (数组或单个值)
     * @return  int     1成功0失败
     * @author  renqingbin
     * @date    20170411  Redis Hmset 命令用于同时将多个 field-value (字段-值)对设置到哈希表中。(设置在哈希表中)
     */
    public function hashMSet($keyName, $value)
    {
        return app('redis')->HMSET($keyName, $value);
    }

    /**
     * 删除hash中某个值
     *
     * @param $key
     * @param $value
     * @return mixed
     * @author renqingbin
     */
    public function hashDel($key, $value)
    {
        return app('redis')->HDEL($key, $value);
    }

    /**
     * 为哈希表 key 中的域 field 的值加上增量 increment
     *
     * @param   $key 哈希表key
     * @param   $member 域名
     * @param   $score  域值
     * @return  int     1成功0失败
     * @author  renqingbin
     * @date    20170411 Redis HINCRBY命令用于增加存储在字段中存储由增量键哈希的数量。
     */
     public function hashIncrby($key, $member, $score)
     {
         return app('redis')->HINCRBY($key, $member, $score);
     }

    /**
     * redis 获取哈希的长度
     *
     * @param   $keyName
     * @return  int
     * @author  renqingbin
     * @date    20170807
     */
    public function hashLen($keyName)
    {
        return app('redis')->HLEN($keyName);
    }

    /**
     * 删除某条缓存
     *
     * @param   $keyName 哈希表key
     * @return  int     1成功0失败
     * @author  renqingbin
     * @date    20170411   删除对应的hash值
     */
    public function del($keyName)
    {
      return app('redis')->DEL($keyName);
    }

    /**
     * 删除有序集合里面的成员
     *
     * @param $keyName
     * @param $value 成员名
     * @return mixed
     * @author renqingbin
     */
    public function sortedDel($keyName, $value)
    {
        return app('redis')->ZREM($keyName, $value);
    }

    /**
     * sorted set将一个或多个 member 元素及其 score 值加入到有序集 key 当中。
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  bool
     * @author  sunchanghao
     * @date    201708128 将一个或多个 member 元素及其 score 值加入到有序集 key 当中
     */
    public function sortedAdd($keyName,$number, $value)
    {
        return app('redis')->ZADD($keyName,$number ,$value);
    }

    /**
     * 通过索引区间返回有序集合成指定区间内的成员(从大到小)。
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  bool
     * @author  sunchanghao
     * @date    201708128
     */
    public function sortedRevRange($keyName,$number1=0, $number2=-1)
    {
        return app('redis')->zRevRange($keyName, $number1, $number2,'WITHSCORES');
    }

    /**
     * 获取有序集合的成员数。
     *
     * @param   $keyName
     * @return  bool
     * @author  sunchanghao
     * @date    201708128
     */
    public function sortedCard($key)
    {
        return app('redis')->ZCARD($key);
    }

    /**
     * 为有序集 key 的成员 member 的 score 值加上增量 increment
     * @param $key
     * @param $score
     * @param $member
     * @return mixed
     */
    public function sortedIncrby($key, $score, $member)
    {
        return app('redis')->ZINCRBY($key, $score, $member);
    }

    /**
     * redis set
     *
     * @param   $keyName
     * @param   $value ()
     * @return  int
     * @author  renqingbin
     * @date    20170411
     */
    public function set($keyName, $value)
    {
        return  app('redis')->set($keyName, $value);
    }

    /**
     * redis get
     *
     * @param   $keyName
     * @return  value
     * @author  renqingbin
     * @date    20170411
     */
    public function get($keyName)
    {
        return app('redis')->get($keyName);
    }

    /**
     * 检查给定 key 是否存在
     *
     * @param   $keyName
     * @return  int
     * @author  sunchanghao
     * @date    2017/8/15
     */
    public function exists($keyName)
    {
        return app('redis')->EXISTS($keyName);
    }

    /**
     * redis 过期时间
     *
     * @param   $keyName
     * @param   $time 时间(单位秒)
     * @return  int
     * @author  renqingbin
     * @date    20170807
     */
    public function expire($keyName, $time)
    {
        return app('redis')->EXPIRE($keyName, $time);
    }

    /**
     * redis 到某个时间点过期
     *
     * @param   $keyName
     * @param   $time unix时间戳 在那个时刻过期
     * @return  int
     * @author  renqingbin
     * @date    20170807
     */
    public function expireAt($keyName, $time)
    {
        return app('redis')->EXPIREAT($keyName, $time);
    }

    /**
     * 为字符转设置过期时间
     *
     * @param $key
     * @param $time
     * @param $value
     * @return mixed
     * @author zhangyuchao
     */
    public function stringSetEx($key,$time,$value)
    {
        return app('redis')->SETEX($key, $time, $value);
    }

    /**
     * 为字符串 key 中的 field 的值加上增量 increment
     *
     * @param   $key 字符串key
     * @param   $score  域值
     * @return  int     1成功0失败
     * @author  renqingbin
     * @date    20170411 。
     */
    public function incrby($key, $score)
    {
        return app('redis')->INCRBY($key, $score);
    }


}
