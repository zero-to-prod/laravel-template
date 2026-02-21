<?php

namespace App\Modules\Api;

enum ErrorCode: string
{
    case unauthorized = 'unauthorized';
    case invalid_credentials = 'invalid_credentials';
}