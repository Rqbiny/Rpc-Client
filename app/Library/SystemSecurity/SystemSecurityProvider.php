<?php

namespace App\Library\SystemSecurity;

use Illuminate\Support\ServiceProvider;

class SystemSecurityProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //系统类中,安全管理类
       $this->app->singleton('Security',\App\Library\SystemSecurity\source\SecurityClass::class);

        //系统加密类,包括密码加密和url加密
        $this->app->singleton('Secret',\App\Library\SystemSecurity\source\SecretClass::class);
    }

    /**
     * 提供的服务
     *
     * @return array
     */
    public function provides()
    {
        return ['Security','Secret'];
    }
}
