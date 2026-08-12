<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Topnav extends Component
{
    public function __construct(public readonly bool $leftNav = false) {}

    public function render(): View|Closure|string
    {
        return view('topnav', ['leftNav' => $this->leftNav]);
    }
}
