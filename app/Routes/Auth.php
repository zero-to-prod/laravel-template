<?php

namespace App\Routes;

use App\Helpers\RendersRoute;

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
