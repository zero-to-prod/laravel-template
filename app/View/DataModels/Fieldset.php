<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;

class Fieldset
{
    use DataModel;

    public const string legend = 'legend';

    public ?string $legend = null;

    public const string name = 'name';

    public ?string $name = null;

    public const string bag = 'bag';

    public string $bag = 'default';

    public const string required = 'required';

    public bool $required = false;

    public const string title = 'title';

    public ?string $title = null;
}
