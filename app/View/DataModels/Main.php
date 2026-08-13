<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\Theme;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Zerotoprod\DataModel\Describe;

class Main
{
    use DataModel;

    public const string main = 'main';
    public const string classnames = 'classnames';

    public ?string $classnames = null;

    public const string adminNav = 'adminNav';

    #[Describe([Describe::default => [AdminNav::class, 'visible']])]
    public bool $adminNav;

    public const string leftNav = 'leftNav';

    #[Describe([Describe::default => [LeftNav::class, 'visible']])]
    public bool $leftNav;

    public const string theme = 'theme';

    #[Describe([Describe::default => [self::class, 'userTheme']])]
    public ?string $theme;

    public function nav(): bool
    {
        return $this->leftNav || $this->adminNav;
    }

    /** @return array<string, mixed> */
    public function topnav(): array
    {
        return [
            Topnav::leftNav => $this->leftNav,
            Topnav::adminNav => $this->adminNav,
        ];
    }

    public static function userTheme(): ?string
    {
        $User = Auth::user();

        return ($User instanceof User ? $User->theme : Theme::auto)->attribute();
    }
}
