<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Routes\Admin;

readonly class AdminNav
{
    use DataModel;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return [
            NavItem::from([NavItem::label => 'Links', NavItem::icon => 'document', NavItem::route => Admin::links]),
            NavItem::from([NavItem::label => 'Users', NavItem::icon => 'user', NavItem::route => Admin::users]),
        ];
    }

    public static function visible(): bool
    {
        return Admin::index->isActive(request());
    }
}
