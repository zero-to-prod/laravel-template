<?php

namespace App\Routes;

use App\Helpers\IsSingleton;

class Web
{
    use IsSingleton;

    public string $home = '/';
    public string $login = '/login';
    public string $logout = '/logout';
    public string $register = '/register';
}