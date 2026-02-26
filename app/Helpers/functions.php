<?php

use App\Modules\Api\Api;
use App\Routes\ApiRoutes;
use App\Routes\WebRoutes;

if (! function_exists('api')) {
    function api(): ApiRoutes
    {
        return ApiRoutes::getInstance();
    }
}

if (! function_exists('web')) {
    function web(): WebRoutes
    {
        return WebRoutes::getInstance();
    }
}

if (! function_exists('api_response')) {
    function api_response(): Api
    {
        return app(Api::class);
    }
}

if (! function_exists('render_url')) {
    function render_url(string $url, array $parameters): string
    {
        foreach ($parameters as $key => $parameter) {
            $url = str_replace("{{$key}}", $parameter, $url);
        }

        return $url;
    }
}