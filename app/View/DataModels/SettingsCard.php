<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Routes\Auth;

class SettingsCard
{
    use DataModel;

    public const string title = 'title';

    public ?string $title = null;

    /** @return list<NavItem> */
    public static function sections(): array
    {
        return [
            NavItem::from([NavItem::label => 'Profile', NavItem::icon => 'user', NavItem::route => Auth::settingsProfile]),
            NavItem::from([NavItem::label => 'Authentication', NavItem::icon => 'key', NavItem::route => Auth::settingsAuthentication]),
            NavItem::from([NavItem::label => 'Appearance', NavItem::icon => 'swatch', NavItem::route => Auth::settingsAppearance]),
        ];
    }

    /** @return array<string, mixed> */
    public function pageHeader(): array
    {
        return [PageHeader::title => $this->title];
    }
}
