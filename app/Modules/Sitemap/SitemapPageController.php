<?php

namespace App\Modules\Sitemap;

use App\Routes\Web;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

readonly class SitemapPageController
{
    public function __invoke(int $page): Response
    {
        $cases = Sitemap::page($page);

        if ($cases === []) {
            abort(404);
        }

        $urls = array_map(
            static fn (Web $Case): string => '    <url><loc>'.url($Case->url()).'</loc>'
                .Sitemap::lastmod($Case).'</url>',
            $cases,
        );

        return new Response(
            implode("\n", [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
                ...$urls,
                '</urlset>',
                '',
            ]),
            ResponseAlias::HTTP_OK,
            ['Content-Type' => 'application/xml; charset=utf-8'],
        );
    }
}
