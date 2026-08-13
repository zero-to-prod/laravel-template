<?php

namespace App\Routes;

use App\Helpers\RendersRoute;

/**
 * The paths behind session authentication.
 *
 * Which index a path belongs to follows what guards it, never how the url reads:
 * these are the ones bound inside the authenticated group, and the pages an
 * authenticated pattern gates. There is no sitemap here and nothing to add one, so
 * a path declared here is never advertised — which is the point for a private page,
 * and an unnoticed omission for a public one.
 */
enum Auth: string
{
    use RendersRoute;

    case dashboard = '/dashboard';
    case settings = '/settings';
    case settingsProfile = '/settings/profile';
    case settingsAuthentication = '/settings/authentication';
    case settingsAppearance = '/settings/appearance';
    case verificationNotice = '/email/verify';
    case verificationVerify = '/email/verify/{id}/{hash}';
    case verificationSend = '/email/verification-notification';
}
