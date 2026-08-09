<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;

class AuthCard
{
    use DataModel;

    public const string title = 'title';

    public ?string $title = null;

    public const string maxWidth = 'maxWidth';

    public string $maxWidth = 'sm:max-w-sm';

    public const string classname = 'classname';

    public string $classname = '';

    public function classes(): string
    {
        return trim("card sm:m-auto sm:mt-24 $this->maxWidth $this->classname");
    }

    /** @return array<string, mixed> */
    public function pageHeader(): array
    {
        return [PageHeader::title => $this->title];
    }
}
