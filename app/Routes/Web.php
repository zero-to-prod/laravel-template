<?php

namespace App\Routes;

use App\Helpers\RendersRoute;

enum Web: string
{
    use RendersRoute;

    case home = '/';
    case llms = '/llms.txt';
    case login = '/login';
    case logout = '/logout';
    case register = '/register';
    case dashboard = '/dashboard';
    case settings = '/settings';
    case settingsProfile = '/settings/profile';
    case settingsAuthentication = '/settings/authentication';
    case settingsAppearance = '/settings/appearance';
    case verificationNotice = '/email/verify';
    case verificationVerify = '/email/verify/{id}/{hash}';
    case verificationSend = '/email/verification-notification';
}
