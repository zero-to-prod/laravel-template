<?php

namespace App\View\Components;

use App\View\DataModels\Main as MainModel;
use App\View\ViewName;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Main extends Component
{
    public function __construct(public readonly ?string $classnames = null) {}

    public function render(): View|Closure|string
    {
        return ViewName::main->render([MainModel::main => [MainModel::classnames => $this->classnames]]);
    }
}
