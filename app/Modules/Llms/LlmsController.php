<?php

namespace App\Modules\Llms;

use App\Helpers\CacheKey;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

readonly class LlmsController
{
    public function __invoke(): Response
    {
        $content = Cache::get(CacheKey::llms->value);

        return new Response(
            is_string($content) ? $content : (string) file_get_contents(resource_path(CacheKey::llms->value)),
            ResponseAlias::HTTP_OK,
            ['Content-Type' => 'text/markdown; charset=utf-8'],
        );
    }
}
