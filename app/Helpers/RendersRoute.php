<?php

namespace App\Helpers;

use Zerotoprod\DataModel\Describe;

trait RendersRoute
{
    use DataModel;

    public const route = 'route';
    #[Describe(['default' => ''])]
    public string $route;
    public const path_params = 'path_params';
    #[Describe(['default' => []])]
    public array $path_params;
    public const params = 'params';
    #[Describe(['default' => []])]
    public array $params;

    public function render(): string
    {
        $query = http_build_query($this->params);
        $url = $this->buildUrlWithPathParams();

        return $query ? "$url?$query" : $url;
    }
    private function buildUrlWithPathParams(): string
    {
        $url = $this->route;

        foreach ($this->path_params as $key => $parameter) {
            $url = str_replace("{{$key}}", $parameter, $url);
        }

        return $url;
    }
}