<?php

namespace App\Modules\Sitemap;

use App\Routes\Web;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

readonly class SitemapController
{
    public function __invoke(): Response
    {
        $urls = array_map(
            static fn (Web $page): string => '    <url><loc>'.url($page->url()).'</loc></url>',
            Web::sitemap(),
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
