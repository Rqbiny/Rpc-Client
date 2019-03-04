/**************************/
/*
*
*                   此文档因为修改了一些东西不太准确，有时间会重写，如有需要当面交流使用方法
*
*/
此mysystem类:
    是存在于\app\Library组件库的-----系统组件组件:

    介绍:
        1. 按照其功能,分别封装到不同的class类中,
            比如:Security类, 为系统安全类


        2. 其中,所有的不同功能的类,都在SystemSecurityFacade服务者中注入,

        3. 而不同的功能,都封装到不同的facade类中.

/**************************/

------------------------------------------------------
 服务提供者:

          App\Library\SystemSecurity\SystemSecurityProvider::class,
------------------------------------------------------


1. 配置 系统安全类( \App\Library\SystemSecurity\source\security组件 ):

        在服务提供者中注入的接口为:

                \App\Library\SystemSecurity\source\securityClass::class

        aliases(别名的配置)

                'MySecurity' => App\Library\SystemSecurity\SystemSecurityFacade::class,


2. SecurityConfig.php   为系统安全的配置文件


        /--------ip防御的配置和说明---------/

        所有的状态码表

            "status" => 500             "info" => "配置文件信息找不到,请检测配置文件的key"
            "status" => 501             "info" => "此ip已经在黑名单中"
            "status" => 502             "info" => "由于你的操作过于频繁, 再三劝说不停, 将直接进行拉黑ip处理, 如果你有异议,请联系管理员"

            "status" => 201             "info" => "此ip已经在白名单中"
            "status" => 203             "info" => "此ip第一次访问此接口"
            "status" => 204             "info" => "此ip已经连续访问,但是还没有到警告阀值"
            "status" => 205             "info" => "您的操作过于频繁,请你0分钟后,再次进行操作,否者严格处理"

        使用的方法有:

        现在有三个方法提供使用:

            \MySecurity::IPIntercept()                  ip防御的基本方法
            \MySecurity::createTable()                  ip防御的mysql数据表的建立
            \MySecurity::addGuardIpToTable()            ip的文件导入到数据库中,黑名单或白名单


        /--------cookie和验证码的共同防御,---------------/

        简单说明:
            验证码可以防御某些接口,但是验证码会消耗服务器性能, 然后用cookie来防范验证码是否合法,如果不合法 ,则返回静态图片

        现在有三个方法提供使用:
         \MySecurity::zymStaticImages()                 生成静态的验证码
         \MySecurity::cookieGenerator()                 生成cookie
         \MySecurity::cookieVerification()              验证cookie
