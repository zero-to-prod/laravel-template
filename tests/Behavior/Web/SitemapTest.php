<?php

use App\Routes\ExcludeFromSitemap;
use App\Routes\Web;

/** @return list<string> */
function sitemapLocations(string $xml): array
{
    preg_match_all('#<loc>(.*?)</loc>#', $xml, $matches);

    return $matches[1];
}

test('the sitemap is served as xml', function (): void {
    $this->get(Web::sitemap->value)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
        ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
});

test('the sitemap lists every route not marked '.ExcludeFromSitemap::class, function (): void {
    $expected = array_map(static fn (Web $page): string => url($page->url()), Web::sitemap());

    expect($expected)->not->toBeEmpty()
        ->and(sitemapLocations((string) $this->get(Web::sitemap->value)->getContent()))->toBe($expected);
});

// The attribute is a claim about each page; this is the claim being checked. A route that
// stops being public, or gains a parameter, has to be excluded or this fails.
test('every url in the sitemap is reachable and indexable', function (): void {
    foreach (sitemapLocations((string) $this->get(Web::sitemap->value)->getContent()) as $loc) {
        $this->get($loc)
            ->assertOk()
            ->assertSee('<meta name="robots" content="all">', false);
    }
});

test('robots.txt points at the sitemap', function (): void {
    $this->get(Web::robots->value)
        ->assertOk()
        ->assertSee('Sitemap: '.url(Web::sitemap->url()), false);
});
