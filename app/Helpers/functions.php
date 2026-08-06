<?php

use App\Modules\Api\Api;

function api_response(): Api
{
    return app(Api::class);
}

/** @param  array<string, string|int>  $parameters */
function render_url(string $url, array $parameters): string
{
    foreach ($parameters as $key => $parameter) {
        $url = str_replace("{{$key}}", (string) $parameter, $url);
    }

    return $url;
}
