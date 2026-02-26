<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Main extends Component
{
    public function __construct(public readonly ?string $classnames = null) {}

    public function render(): View|Closure|string
    {
        return view(
            view: 'main',
            data: ['classnames' => $this->classnames]
        );
    }
}
