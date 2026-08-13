<?php

namespace App\Modules\Sitemap;

use App\Routes\Web;

/**
 * The advertised paths, split into the numbered documents an index file references.
 *
 * The protocol caps how many paths one document may carry, and a document over the cap
 * is rejected whole rather than truncated, so the paths are split here and the root
 * document indexes the parts: a path count that outgrows the cap adds a part instead of
 * silently unpublishing the site. Numbering is one-based because it is read straight
 * into the child urls, so nothing downstream recomputes it. A modification time is the
 * mtime of the file the page renders from and is omitted when there is no such file —
 * a time that moves while the page does not teaches a crawler to disregard it, which
 * costs more than declaring nothing.
 */
final readonly class Sitemap
{
    public const int urlLimit = 50_000;

    /** @return array<int, list<Web>> */
    public static function pages(): array
    {
        $pages = [];

        foreach (array_chunk(Web::sitemap(), self::urlLimit) as $index => $cases) {
            $pages[$index + 1] = $cases;
        }

        return $pages;
    }

    /** @return list<Web> */
    public static function page(int $page): array
    {
        return self::pages()[$page] ?? [];
    }

    public static function lastmod(Web ...$cases): string
    {
        $times = array_filter(array_map(self::modified(...), $cases));

        return $times === [] ? '' : '<lastmod>'.date(DATE_W3C, max($times)).'</lastmod>';
    }

    private static function modified(Web $Web): ?int
    {
        $path = trim($Web->url(), '/');
        $file = resource_path('views/pages/'.($path === '' ? '' : $path.'/').'index.blade.php');

        return is_file($file) ? (filemtime($file) ?: null) : null;
    }
}
