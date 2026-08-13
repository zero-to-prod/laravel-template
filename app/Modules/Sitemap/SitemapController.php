<?php

namespace App\Modules\Sitemap;

use App\Routes\Web;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

readonly class SitemapController
{
    public function __invoke(): Response
    {
        $sitemaps = [];

        foreach (Sitemap::pages() as $page => $cases) {
            $sitemaps[] = '    <sitemap><loc>'.url(Web::sitemapPage->url(['page' => $page])).'</loc>'
                .Sitemap::lastmod(...$cases).'</sitemap>';
        }

        return new Response(
            implode("\n", [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
                ...$sitemaps,
                '</sitemapindex>',
                '',
            ]),
            ResponseAlias::HTTP_OK,
            ['Content-Type' => 'application/xml; charset=utf-8'],
        );
    }
}
