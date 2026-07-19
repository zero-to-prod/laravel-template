<?php

namespace App\Routes;

enum MiddlewareTag: string
{
    case web = 'web';
    case api = 'api';
    case auth = 'auth';
    case verified = 'verified';
    case sanctum = 'auth:sanctum';
}
