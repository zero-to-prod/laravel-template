<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Component;

class Errors extends Component
{
    public function __construct(public readonly ViewErrorBag $errors, public $take = null, public readonly ?string $classname = null)
    {
    }

    public function render(): View|Closure|string
    {
        return view(
            view: 'errors',
            data: [
                'ViewErrorBag' => $this->errors,
                'take' => $this->take,
                'classname' => $this->classname
            ]
        );
    }
}
