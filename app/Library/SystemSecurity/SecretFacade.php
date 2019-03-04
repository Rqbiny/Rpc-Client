<?php

namespace App\Library\SystemSecurity;

class SecretFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Secret';
    }
}
