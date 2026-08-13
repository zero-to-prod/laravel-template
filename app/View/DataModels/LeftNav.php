<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Routes\Web;

readonly class LeftNav
{
    use DataModel;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return [
            NavItem::from([NavItem::label => 'Home', NavItem::icon => 'home', NavItem::route => Web::home]),
        ];
    }

    public static function visible(): bool
    {
        return request()->user() !== null
            && ! SettingsNav::visible()
            && ! AdminNav::visible();
    }
}
