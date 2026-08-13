<?php

namespace App\Routes;

use App\Helpers\RendersRoute;
use ReflectionEnumBackedCase;

/**
 * The paths a guest can reach.
 *
 * Which index a path belongs to follows what guards it, never how the url reads:
 * these are the ones bound without a guard, and the pages no pattern gates. This is
 * the only index with a sitemap, so a case is advertised unless it says otherwise,
 * and every advertised one is held to being reachable and indexable by a stranger —
 * which makes declaring a private path here loud, while declaring a public one
 * elsewhere is silent. Redirects out of a failed request land on cases here too.
 */
enum Web: string
{
    use RendersRoute;

    case home = '/';
    #[ExcludeFromSitemap]
    #[AdminLink(order: 2)]
    case llms = '/llms.txt';
    #[ExcludeFromSitemap]
    #[AdminLink]
    case robots = '/robots.txt';
    #[ExcludeFromSitemap]
    #[AdminLink]
    case sitemap = '/sitemap.xml';
    #[ExcludeFromSitemap]
    #[AdminLink]
    case openapi = '/openapi.json';
    case contact = '/contact';
    case privacyPolicy = '/privacy-policy';
    case termsOfService = '/terms-of-service';
    #[ExcludeFromSitemap]
    case login = '/login';
    #[ExcludeFromSitemap]
    case logout = '/logout';
    #[ExcludeFromSitemap]
    case register = '/register';

    /** @return list<self> */
    public static function sitemap(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $case): bool => new ReflectionEnumBackedCase(self::class, $case->name)
                ->getAttributes(ExcludeFromSitemap::class) === [],
        ));
    }
}
