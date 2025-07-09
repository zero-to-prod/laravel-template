<?php

use App\Routes\ApiRoutes;
use App\Routes\WebRoutes;

if (!function_exists('api')) {
    function api(): ApiRoutes
    {
        return ApiRoutes::getInstance();
    }
}

if (!function_exists('web')) {
    function web(): WebRoutes
    {
        return WebRoutes::getInstance();
    }
}