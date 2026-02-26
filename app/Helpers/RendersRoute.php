<?php

namespace App\Helpers;

use Zerotoprod\DataModel\Describe;

trait RendersRoute
{
    use DataModel;

    /** @see $route */
    public const string route = 'route';

    #[Describe(['default' => ''])]
    public string $route;

    /** @see $path_params */
    public const string path_params = 'path_params';

    #[Describe(['default' => []])]
    public array $path_params;

    /** @see $params */
    public const string params = 'params';

    #[Describe(['default' => []])]
    public array $params;

    public function render(): string
    {
        $query = http_build_query($this->params);
        $route = render_url($this->route, $this->path_params);

        return $query
            ? $route.'?'.$query
            : $route;
    }
}
