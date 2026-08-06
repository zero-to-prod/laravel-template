<?php

use App\Modules\Api\Api;
use App\Routes\Web;

if (! function_exists('web')) {
    function web(): Web
    {
        return Web::getInstance();
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
