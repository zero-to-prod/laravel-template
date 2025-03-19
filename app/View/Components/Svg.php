<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Svg extends Component
{
    public const name = 'name';
    public const classname = 'classname';

    public function __construct(public readonly string $name, public readonly ?string $classname = null)
    {
    }

    public function render(): View
    {
        return view(
            view: 'svg',
            data: [
                self::name => $this->name,
                self::classname => $this->classname,
            ]
        );
    }
}
