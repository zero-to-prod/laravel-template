<?php

namespace App\View\DataModels;

use App\Helpers\SvgName;
use App\Routes\Admin;
use ReflectionEnumUnitCase;

enum AdminNav
{
    #[NavItem([NavItem::label => 'Users', NavItem::icon => SvgName::user, NavItem::route => Admin::users])]
    case users;

    #[NavItem([NavItem::label => 'Sessions', NavItem::icon => SvgName::desktop, NavItem::route => Admin::sessions])]
    case sessions;

    #[NavItem([NavItem::label => 'Content', NavItem::icon => SvgName::document, NavItem::route => Admin::content])]
    case content;

    #[NavItem([NavItem::label => 'Links', NavItem::icon => SvgName::document, NavItem::route => Admin::links])]
    case links;

    #[NavItem([NavItem::label => 'Logs', NavItem::icon => SvgName::command_line, NavItem::route => Admin::logs])]
    case logs;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return array_map(
            static fn (self $AdminNav): NavItem => $AdminNav->item(),
            self::cases(),
        );
    }

    public function item(): NavItem
    {
        $attributes = new ReflectionEnumUnitCase(self::class, $this->name)->getAttributes(NavItem::class);

        return $attributes[0]->newInstance();
    }

    public static function visible(): bool
    {
        return Admin::index->isActive(request());
    }
}
