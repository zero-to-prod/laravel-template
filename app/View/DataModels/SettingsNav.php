<?php

namespace App\View\DataModels;

use App\Helpers\SvgName;
use App\Routes\Auth;
use LogicException;
use ReflectionEnumUnitCase;

enum SettingsNav
{
    #[NavItem([NavItem::label => 'Profile', NavItem::icon => SvgName::user, NavItem::route => Auth::settingsProfile])]
    case profile;

    #[NavItem([NavItem::label => 'Appearance', NavItem::icon => SvgName::swatch, NavItem::route => Auth::settingsAppearance])]
    case appearance;

    #[NavItem([NavItem::label => 'Security', NavItem::icon => SvgName::key, NavItem::route => Auth::settingsSecurity])]
    case security;

    #[NavItem([NavItem::label => 'Credentials', NavItem::icon => SvgName::command_line, NavItem::route => Auth::settingsCredentials, NavItem::nested => true])]
    case credentials;

    #[NavItem([NavItem::label => 'Sessions', NavItem::icon => SvgName::desktop, NavItem::route => Auth::settingsSessions, NavItem::nested => true])]
    case sessions;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return array_map(
            static fn (self $SettingsNav): NavItem => $SettingsNav->item(),
            self::cases(),
        );
    }

    public function item(): NavItem
    {
        $attributes = new ReflectionEnumUnitCase(self::class, $this->name)->getAttributes(NavItem::class);
        $arguments = $attributes[0]->getArguments();
        $item = $arguments[0] ?? null;

        if (! is_array($item)) {
            throw new LogicException('Settings navigation cases must describe a navigation item.');
        }

        $attributes = [];

        foreach ($item as $key => $value) {
            if (! is_string($key)) {
                throw new LogicException('Settings navigation attributes must be named.');
            }

            $attributes[$key] = $value;
        }

        return new NavItem($attributes);
    }

    public static function visible(): bool
    {
        return Auth::settings->isActive(request());
    }
}
