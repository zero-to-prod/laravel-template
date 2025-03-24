<?php

use App\Routes\Api;
use App\Routes\RouteManager;
use App\Routes\Web;

if (!function_exists('api')) {
    function api(): Api
    {
        return Api::getInstance();
    }
}

if (!function_exists('web')) {
    function web(): Web
    {
        return Web::getInstance();
    }
}

if (!function_exists('r')) {
    function r(): RouteManager
    {
        return RouteManager::getInstance();
    }
}