<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\Initials;
use App\Helpers\Role;
use App\Helpers\SvgName;
use App\Models\User;
use App\Routes\Admin;
use App\Routes\Auth;
use App\Routes\Web;

class UserMenu
{
    use DataModel;

    public const string name = 'name';

    public string $name = '';

    public const string email = 'email';

    public string $email = '';

    public const string picture = 'picture';

    public ?string $picture = null;

    public function picture(): ?string
    {
        return $this->picture;
    }

    /** @return list<NavItem> */
    public static function items(): array
    {
        return [
            ...(self::isAdmin()
                ? [NavItem::from([NavItem::label => 'Admin', NavItem::icon => SvgName::command_line, NavItem::route => Admin::index])]
                : []),
            NavItem::from([NavItem::label => 'Settings', NavItem::icon => SvgName::gear, NavItem::route => Auth::settingsProfile]),
            NavItem::from([NavItem::label => 'Logout', NavItem::icon => SvgName::logout, NavItem::route => Web::logout]),
        ];
    }

    private static function isAdmin(): bool
    {
        $User = auth()->guard()->user();

        return $User instanceof User && $User->hasRole(Role::admin->value);
    }

    public function initials(): string
    {
        return Initials::from($this->name);
    }
}
