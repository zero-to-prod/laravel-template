<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;

class PageHeader
{
    use DataModel;

    public const string title = 'title';

    public ?string $title = null;

    public const string classname = 'classname';

    public string $classname = 'card-title';
}
