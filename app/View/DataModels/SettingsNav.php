<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Routes\Auth;

readonly class SettingsNav
{
    use DataModel;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return [
            NavItem::from([NavItem::label => 'Profile', NavItem::icon => 'user', NavItem::route => Auth::settingsProfile]),
            NavItem::from([NavItem::label => 'Authentication', NavItem::icon => 'key', NavItem::route => Auth::settingsAuthentication]),
            NavItem::from([NavItem::label => 'Credentials', NavItem::icon => 'command-line', NavItem::route => Auth::settingsCredentials, NavItem::nested => true]),
            NavItem::from([NavItem::label => 'Appearance', NavItem::icon => 'swatch', NavItem::route => Auth::settingsAppearance]),
        ];
    }

    public static function visible(): bool
    {
        return Auth::settings->isActive(request());
    }
}
