<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\SvgName;
use App\Routes\Auth;

readonly class SettingsNav
{
    use DataModel;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return [
            NavItem::from([NavItem::label => 'Profile', NavItem::icon => SvgName::user, NavItem::route => Auth::settingsProfile]),
            NavItem::from([NavItem::label => 'Security', NavItem::icon => SvgName::key, NavItem::route => Auth::settingsSecurity]),
            NavItem::from([NavItem::label => 'Credentials', NavItem::icon => SvgName::command_line, NavItem::route => Auth::settingsCredentials, NavItem::nested => true]),
            NavItem::from([NavItem::label => 'Appearance', NavItem::icon => SvgName::swatch, NavItem::route => Auth::settingsAppearance]),
        ];
    }

    public static function visible(): bool
    {
        return Auth::settings->isActive(request());
    }
}
