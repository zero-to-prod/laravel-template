<?php

namespace App\View;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as Views;

/**
 * The views this application renders by name. Folio pages are routed by file
 * path and anonymous components by tag, so neither is named here: a case is a
 * view something asks for. A whole directory of interchangeable views is a case on
 * the sibling registry instead.
 */
enum ViewName: string
{
    case main = 'main';

    /** @param  array<string, mixed>  $data */
    public function render(array $data = []): View
    {
        return view($this->value, $data);
    }

    public function exists(): bool
    {
        return Views::exists($this->value);
    }
}
