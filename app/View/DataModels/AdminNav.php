<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\SvgName;
use App\Routes\Admin;

readonly class AdminNav
{
    use DataModel;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return [
            NavItem::from([NavItem::label => 'Users', NavItem::icon => SvgName::user, NavItem::route => Admin::users]),
            NavItem::from([NavItem::label => 'Links', NavItem::icon => SvgName::document, NavItem::route => Admin::links]),
        ];
    }

    public static function visible(): bool
    {
        return Admin::index->isActive(request());
    }
}
