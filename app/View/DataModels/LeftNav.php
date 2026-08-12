<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Routes\Web;
use Illuminate\Support\Facades\Auth;

readonly class LeftNav
{
    use DataModel;

    /** @return list<array{label: string, icon: string, route: Web}> */
    public static function items(): array
    {
        return [
            ['label' => 'Home', 'icon' => 'home', 'route' => Web::home],
        ];
    }

    public static function visible(): bool
    {
        return Auth::check() && ! Web::settings->isActive(request());
    }
}
