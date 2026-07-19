<?php

namespace App\Helpers;

enum HttpHeader: string
{
    case HxRequest = 'HX-Request';
    case HxRedirect = 'HX-Redirect';
    case ContentType = 'Content-Type';
    case Authorization = 'Authorization';
}
