<?php

namespace App\Modules\Robots;

use App\Helpers\CacheKey;
use App\Routes\Web;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

readonly class RobotsController
{
    public function __invoke(): Response
    {
        $content = Cache::get(CacheKey::robots->value);

        return new Response(
            (is_string($content) ? $content : (string) file_get_contents(resource_path(CacheKey::robots->value)))
            ."\nSitemap: ".url(Web::sitemap->url())."\n",
            ResponseAlias::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=utf-8'],
        );
    }
}
