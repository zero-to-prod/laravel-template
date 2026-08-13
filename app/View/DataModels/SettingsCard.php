<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;

class SettingsCard
{
    use DataModel;

    public const string title = 'title';

    public ?string $title = null;

    /** @return array<string, mixed> */
    public function pageHeader(): array
    {
        return [PageHeader::title => $this->title];
    }
}
