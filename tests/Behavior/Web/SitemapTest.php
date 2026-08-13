<?php

use App\Modules\Sitemap\Sitemap;
use App\Routes\ExcludeFromSitemap;
use App\Routes\Web;

/** @return list<string> */
function sitemapLocations(string $xml): array
{
    preg_match_all('#<loc>(.*?)</loc>#', $xml, $matches);

    return $matches[1];
}

/** @return list<string> */
function sitemapModifications(string $xml): array
{
    preg_match_all('#<lastmod>(.*?)</lastmod>#', $xml, $matches);

    return $matches[1];
}

test('the root document is a sitemap index served as xml', function (): void {
    $this->get(Web::sitemap->value)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
        ->assertSee('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
        ->assertDontSee('<urlset', false);
});

test('the index references one numbered sitemap per page, and every page is served as xml', function (): void {
    $expected = array_map(
        static fn (int $page): string => url(Web::sitemapPage->url(['page' => $page])),
        array_keys(Sitemap::pages()),
    );

    $index = (string) $this->get(Web::sitemap->value)->getContent();

    expect($expected)->not->toBeEmpty()
        ->and(sitemapLocations($index))->toBe($expected);

    foreach ($expected as $loc) {
        $this->get($loc)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
    }
});

// The referenced documents are the whole of the sitemap, so together they carry exactly the
// advertised paths, in order — a page missing from one of them leaves the site's index
// without it while the root document still looks complete.
test('the pages together list every route not marked '.ExcludeFromSitemap::class, function (): void {
    $expected = array_map(static fn (Web $page): string => url($page->url()), Web::sitemap());

    $index = (string) $this->get(Web::sitemap->value)->getContent();
    $listed = [];

    foreach (sitemapLocations($index) as $sitemap) {
        $listed = [...$listed, ...sitemapLocations((string) $this->get($sitemap)->getContent())];
    }

    expect($expected)->not->toBeEmpty()->and($listed)->toBe($expected);
});

// The attribute is a claim about each page; this is the claim being checked. A route that
// stops being public, or gains a parameter, has to be excluded or this fails.
test('every url in the sitemap is reachable and indexable', function (): void {
    $index = (string) $this->get(Web::sitemap->value)->getContent();

    foreach (sitemapLocations($index) as $sitemap) {
        foreach (sitemapLocations((string) $this->get($sitemap)->getContent()) as $loc) {
            $this->get($loc)
                ->assertOk()
                ->assertSee('<meta name="robots" content="all">', false);
        }
    }
});

// A modification time a crawler cannot parse is worse than declaring none, and one that
// moves on its own is why this is read off a file rather than a clock.
test('every modification time is a w3c datetime, in the index and in the pages', function (): void {
    $index = (string) $this->get(Web::sitemap->value)->getContent();
    $times = sitemapModifications($index);

    foreach (sitemapLocations($index) as $sitemap) {
        $times = [...$times, ...sitemapModifications((string) $this->get($sitemap)->getContent())];
    }

    expect($times)->not->toBeEmpty();

    foreach ($times as $time) {
        expect(DateTimeImmutable::createFromFormat(DATE_W3C, $time))->toBeInstanceOf(DateTimeImmutable::class);
    }
});

// Numbering is the whole of addressing a page, so a number no page answers to is not a
// document with nothing in it: an empty one invites a crawler to drop what it already has.
test('a page number outside the range is not found', function (): void {
    $this->get(Web::sitemapPage->url(['page' => count(Sitemap::pages()) + 1]))->assertNotFound();
    $this->get(Web::sitemapPage->url(['page' => 0]))->assertNotFound();
});

// The cap is the protocol's, not a preference, and it is what makes splitting necessary at
// all: a document over it is rejected whole by the crawler that reads it.
test('no page carries more paths than the protocol allows', function (): void {
    expect(Sitemap::urlLimit)->toBe(50_000)
        ->and(Sitemap::pages())->not->toBeEmpty();

    foreach (Sitemap::pages() as $cases) {
        expect(count($cases))->toBeLessThanOrEqual(Sitemap::urlLimit);
    }
});

// A crawler reads the index and then every document it names, so a limit shared across
// them is spent on the first visit — and a refused fetch is read as the sitemap being
// gone rather than as being asked to slow down, which unpublishes the site quietly.
test('reading the index and every page it names is not rate limited', function (): void {
    for ($i = 0; $i < 3; $i++) {
        $index = $this->get(Web::sitemap->value)->assertOk();

        foreach (sitemapLocations((string) $index->getContent()) as $sitemap) {
            $this->get($sitemap)->assertOk();
        }
    }
});

test('robots.txt points at the sitemap index', function (): void {
    $this->get(Web::robots->value)
        ->assertOk()
        ->assertSee('Sitemap: '.url(Web::sitemap->url()), false);
});
