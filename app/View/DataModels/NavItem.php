<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\SvgName;
use App\Routes\Admin;
use App\Routes\Auth;
use App\Routes\Web;
use Zerotoprod\DataModel\Describe;

readonly class NavItem
{
    use DataModel;

    public const string label = 'label';

    #[Describe([Describe::required => true])]
    public string $label;

    public const string icon = 'icon';

    #[Describe([Describe::required => true])]
    public SvgName $icon;

    public const string route = 'route';

    #[Describe([Describe::required => true])]
    public Admin|Auth|Web $route;

    public const string nested = 'nested';

    #[Describe([Describe::default => false])]
    public bool $nested;

    public function url(): string
    {
        return $this->route->url();
    }

    public function active(): bool
    {
        return $this->nested
            ? $this->route->isActive(request())
            : $this->route->isExact(request());
    }

    /** @return array<string, mixed> */
    public function svg(): array
    {
        return [
            Svg::name => $this->icon,
            Svg::classname => 'h-4 w-4 opacity-70',
        ];
    }
}
