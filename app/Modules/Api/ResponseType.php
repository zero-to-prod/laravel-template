<?php

namespace App\Modules\Api;

enum ResponseType: string
{
    case token = 'token';
    case authorized = 'authorized';
    case logout = 'logout';
    case error = 'error';
}