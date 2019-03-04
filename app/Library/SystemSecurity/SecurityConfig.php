<?php

return [

    /**
     *        验证码的配置
     */

    'yzmName'    =>    '',                                       //session中的 验证码的名称, 默认为(yzmCode)


    /**
     *        cookie防护的配置,
     *
     *          其基本原理是:
     *                      1.以免别人不停的请求验证吗接口,但是验证码使用gd库生存,会大量消耗服务器性能.
     *                      2. 此刻可以使用,验证码静态图片,如果非法访问,直接让其访问静态图片,如果在访问其他接口,则验证码会验证失败,
     *
     *                      3. 最好使用file_get_contents(), 然后设置头,这样隐秘型最好,如果重定向会被发现,不够严密
     *
     *          现在有三个方法提供使用:    zymStaticImages(),cookieGenerator(),cookieVerification()
     */
    'cookieGuardConfig'=> [

        //有可能多个接口都会使用到这个cookie验证, 默认的cookie名称为default, 还可以自定义名称
        'cookiename' => [
            'default'   =>  ['key' => 'cookieGuardCode', 'value' => '测试'],                           //有可能多个地方是否
            'Guardyzm'     =>  ['key' => 'aa', 'value' => 'bb'],                              //随便起一个名称
        ],

        //生成验证码的存放地址
        'storageAddress' => '/tmp/yzm',                                                      //一个目录
    ],

    /**
     *        ip防火墙的配置
     *         IP防护的原理:
     *              1. 首先获取用的远程ip
     *              2. 为每个IP做一个hash类型的计时器               //记录开始时间戳,和计数器,  字段有startTime和 num
     *              3. 每个IP都有一定时间的生存期,过期将清空
     *              4. 当某IP存在的时候,继续访问,其计数器累加+1,但是生存时长重置
     *              5. 当某IP不存在的时候,则和第一次访问一样,记录开始时间和计数器
     *
     *              **重点::::::当某一个操作,空闲多长时间后,自动清空,如果间隔不到expire时,则会一直触发ip次数记录**
     *
     *              ----------对IP的判断--------------
     *              1. 当某IP连续访问超过了某一个阀值时,开始警告,提示不要在访问了
     *              2. 当某IP了连续访问超过了一个极限时,直接拉入黑名单,并通知他直接封号
     *              3. 如果是白名单,则过滤,不考虑在内.
     *                  1. 白名单设置在配置文件或数据库中        set
     *                  2. 在redis中配置到list中              get
     *              4. 如果是黑名单,直接拦截.
     *                  1. 黑名单直接插入数据库               set
     *                  2. 在redis中配置到list中             get
     *
     *              ----------对IP的回收机制---------------------
     *              1. 每个IP在redis中hash类型中,设置expireTime时间
     *
     *              2. 重新载入到缓存中的ip,黑白名单的list做一个人过期时间,定期从数据库中更新缓存(IPInterceptListTime)
     *
     *              ----------黑名单的配置和基本配置的联系与区别-----------
     *
     *              1. 系统里面可能需要防范多个接口,但是由于不同的接口,最好不要公用同一个ip缓存key值---IPInterceptExistence,其余都可以使用默认参数(IPIntercept_default里的值)
     *
     *              2. 所有的参数都可以自定义,如果没有赋值,则自动调用IPIntercept_default里面配置信息.
     *
     *              3. 重点,再次强调::::::一般只需要自定义---IPInterceptExistence,更改每个接口存放ip的hash的key名
     *
     *              4. 重点:::::默认情况是不开启,系统自动拉入黑名单的,必须手动设置IPIntercepRecordToDatabase为true   ----> 补充:如果是肉机进行攻击,容易勿伤普通用户
     *
     *      现在有三个方法提供使用:    IPIntercept(),createTable(),addGuardIpToTable()
     */

    'IPIntercept_default'    =>[                // ip 防火墙的配置
        //------------------基本配置信息-------------------------
        //IP生存时间
        'expireTime'         =>         3600,

        //IP的hash中的key
        'IPInterceptExistence'   =>     'SystemSecurity_Security_IPIntercept_Existence_Hash_',

        //  警告的阀值, 操作阀值将会警告.
        'warnningTime'          =>      3,

        // 警告提示语,  # ExpireTime值的占位符
        'warnningLanag'         =>      '您的操作过于频繁,请你#分钟后,再次进行操作,否者严格处理' ,

        // 触发拉入黑名单的阀值
        'dieTime'               =>      6,

        // 拉黑提示语,
        'dieLanguage'  =>'由于你的操作过于频繁, 再三劝说不停, 将直接进行拉黑ip处理, 如果你有异议,请联系管理员',


        // ----------------------黑白名单的配置信息--------------------------------
        // ---------------------------------------------------------------------

        // 在数据库生存对应的黑名单数据表的名称
        'tableNameOfDatabase'   =>      'data_guarded_ip',

        // 执行文件添加到数据库中
        'addGuardIPofFIle'      =>      '',

        // 配置黑白名单的list的key
        'IPInterceptBlackList'  =>      'SystemSecurity_Security_IPIntercept_Black_List',
        'IPInterceptWhiteList'  =>      'SystemSecurity_Security_IPIntercept_White_List',

        // 黑白名单的过期时间(定时删除IPInterceptExistence 的里面的缓存,并且重新加载数据库ip数据)
        'IPInterceptListTime'   =>     3600 * 24 * 30 * 3 , //三个月

        //是否自动添加到数据库黑名单        默认为false,不加入黑名单的数据库和缓存,但是必须等待此ip在内存中消失位置      一个标志位
        'IPIntercepRecordToDatabase'=>    '',//     true/false

        //  ip防护的白名单
        'whiteList'  =>  [
           // '192.168.20.102',
        ],

    ],
];
