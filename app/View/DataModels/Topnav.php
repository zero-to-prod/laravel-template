<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;

class Topnav
{
    use DataModel;

    public const string topnav = 'topnav';
    public const string leftNav = 'leftNav';

    public bool $leftNav = false;

    public const string adminNav = 'adminNav';

    public bool $adminNav = false;

    public function nav(): bool
    {
        return $this->leftNav || $this->adminNav;
    }

    /**
     * The dropdown is the small-screen face of whichever rail the page carries.
     *
     * @return list<NavItem>
     */
    public function items(): array
    {
        return $this->adminNav ? AdminNav::items() : LeftNav::items();
    }
}
