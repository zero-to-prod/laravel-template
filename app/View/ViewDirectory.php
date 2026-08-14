<?php

namespace App\View;

use App\Helpers\SvgName;
use Illuminate\Support\Facades\View as Views;

/**
 * The directories of interchangeable views this application addresses by name.
 * A case is the prefix, never a view: the view is only known at render time,
 * so it is qualified rather than rendered. A single named view is a case on the
 * sibling registry instead.
 */
enum ViewDirectory: string
{
    case svg = 'svg';

    public function qualify(SvgName $name): string
    {
        return $this->value.'.'.$name->value;
    }

    public function has(SvgName $name): bool
    {
        return Views::exists($this->qualify($name));
    }
}
