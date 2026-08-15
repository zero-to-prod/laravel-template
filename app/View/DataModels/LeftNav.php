<?php

namespace App\View\DataModels;

use App\Helpers\SvgName;
use App\Routes\Web;
use LogicException;
use ReflectionEnumUnitCase;

enum LeftNav
{
    #[NavItem([NavItem::label => 'Home', NavItem::icon => SvgName::home, NavItem::route => Web::home])]
    case home;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return array_map(
            static fn (self $LeftNav): NavItem => $LeftNav->item(),
            self::cases(),
        );
    }

    public function item(): NavItem
    {
        $attributes = new ReflectionEnumUnitCase(self::class, $this->name)->getAttributes(NavItem::class);
        $arguments = $attributes[0]->getArguments();
        $item = $arguments[0] ?? null;

        if (! is_array($item)) {
            throw new LogicException('Left navigation cases must describe a navigation item.');
        }

        $attributes = [];

        foreach ($item as $key => $value) {
            if (! is_string($key)) {
                throw new LogicException('Left navigation attributes must be named.');
            }

            $attributes[$key] = $value;
        }

        return new NavItem($attributes);
    }

    public static function visible(): bool
    {
        return request()->user() !== null
            && ! SettingsNav::visible()
            && ! AdminNav::visible();
    }
}
