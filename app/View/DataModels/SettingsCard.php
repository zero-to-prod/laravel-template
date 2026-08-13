<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Routes\Auth;

class SettingsCard
{
    use DataModel;

    public const string title = 'title';

    public ?string $title = null;

    /** @return list<array{label: string, icon: string, route: Auth}> */
    public static function sections(): array
    {
        return [
            ['label' => 'Profile', 'icon' => 'user', 'route' => Auth::settingsProfile],
            ['label' => 'Authentication', 'icon' => 'key', 'route' => Auth::settingsAuthentication],
            ['label' => 'Appearance', 'icon' => 'swatch', 'route' => Auth::settingsAppearance],
        ];
    }

    /** @return array<string, mixed> */
    public function pageHeader(): array
    {
        return [PageHeader::title => $this->title];
    }
}
