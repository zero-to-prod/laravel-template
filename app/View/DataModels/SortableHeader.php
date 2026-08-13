<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\SortDirection;
use Zerotoprod\DataModel\Describe;

class SortableHeader
{
    use DataModel;

    public const string label = 'label';

    #[Describe([Describe::required => true])]
    public string $label;

    public const string url = 'url';

    #[Describe([Describe::required => true])]
    public string $url;

    public const string direction = 'direction';

    #[Describe([Describe::required => true])]
    public SortDirection $direction;

    public const string sorted = 'sorted';

    public bool $sorted = false;

    public const string title = 'title';

    public ?string $title = null;

    public function ariaSort(): string
    {
        return $this->sorted ? $this->direction->aria() : 'none';
    }

    /** @return array<string, mixed> */
    public function svg(): array
    {
        return [
            Svg::name => $this->direction->icon(),
            Svg::classname => 'h-3 w-3 '.($this->sorted ? 'opacity-70' : 'opacity-0 group-hover:opacity-40'),
        ];
    }
}
