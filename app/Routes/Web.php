<?php

namespace App\Routes;

use App\Helpers\RendersRoute;
use ReflectionEnumBackedCase;

enum Web: string
{
    use RendersRoute;

    case home = '/';
    #[ExcludeFromSitemap]
    case llms = '/llms.txt';
    #[ExcludeFromSitemap]
    case robots = '/robots.txt';
    #[ExcludeFromSitemap]
    case sitemap = '/sitemap.xml';
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
            static fn (self $case): bool => (new ReflectionEnumBackedCase(self::class, $case->name))
                ->getAttributes(ExcludeFromSitemap::class) === [],
        ));
    }
}
