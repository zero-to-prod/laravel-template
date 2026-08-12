<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Routes\Web;

class SettingsCard
{
    use DataModel;

    public const string title = 'title';

    public ?string $title = null;

    /** @return list<array{label: string, icon: string, route: Web}> */
    public static function sections(): array
    {
        return [
            ['label' => 'Profile', 'icon' => 'user', 'route' => Web::settingsProfile],
            ['label' => 'Authentication', 'icon' => 'key', 'route' => Web::settingsAuthentication],
            ['label' => 'Appearance', 'icon' => 'swatch', 'route' => Web::settingsAppearance],
        ];
    }

    /** @return array<string, mixed> */
    public function pageHeader(): array
    {
        return [PageHeader::title => $this->title];
    }
}
