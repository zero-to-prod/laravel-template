<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\Role;
use App\Models\User;
use App\Routes\Admin;
use App\Routes\Auth;
use App\Routes\Web;
use Illuminate\Support\Str;

class UserMenu
{
    use DataModel;

    public const string name = 'name';

    public string $name = '';

    public const string email = 'email';

    public string $email = '';

    public const string picture = 'picture';

    public ?string $picture = null;

    /** @return list<NavItem> */
    public static function items(): array
    {
        return [
            ...(self::isAdmin()
                ? [NavItem::from([NavItem::label => 'Admin', NavItem::icon => 'command-line', NavItem::route => Admin::index])]
                : []),
            NavItem::from([NavItem::label => 'Settings', NavItem::icon => 'gear', NavItem::route => Auth::settingsProfile]),
            NavItem::from([NavItem::label => 'Logout', NavItem::icon => 'logout', NavItem::route => Web::logout]),
        ];
    }

    private static function isAdmin(): bool
    {
        $User = auth()->guard()->user();

        return $User instanceof User && $User->hasRole(Role::admin->value);
    }

    public function initials(): string
    {
        $words = array_values(array_filter(explode(' ', Str::squish($this->name))));

        if ($words === []) {
            return '?';
        }

        $last = count($words) > 1 ? Str::substr(end($words), 0, 1) : '';

        return Str::upper(Str::substr($words[0], 0, 1).$last);
    }
}
