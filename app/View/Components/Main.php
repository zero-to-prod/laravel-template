<?php

namespace App\View\Components;

use App\Helpers\Theme;
use App\Models\User;
use App\View\DataModels\LeftNav;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Main extends Component
{
    public function __construct(public readonly ?string $classnames = null) {}

    public function render(): View|Closure|string
    {
        $User = Auth::user();

        return view(
            view: 'main',
            data: [
                'classnames' => $this->classnames,
                'leftNav' => LeftNav::visible(),
                'theme' => ($User instanceof User ? $User->theme : Theme::auto)->attribute(),
            ]
        );
    }
}
