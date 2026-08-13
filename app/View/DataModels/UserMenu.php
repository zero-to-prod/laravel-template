<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
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

    /** @return list<NavItem> */
    public static function items(): array
    {
        return [
            NavItem::from([NavItem::label => 'Settings', NavItem::icon => 'gear', NavItem::route => Auth::settingsProfile]),
            NavItem::from([NavItem::label => 'Logout', NavItem::icon => 'logout', NavItem::route => Web::logout]),
        ];
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
