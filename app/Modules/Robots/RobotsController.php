<?php

namespace App\Modules\Robots;

use App\Routes\Web;
use Illuminate\Http\Response;

readonly class RobotsController
{
    public function __invoke(): Response
    {
        return new Response(
            (string) file_get_contents(resource_path('robots.txt'))
            ."\nSitemap: ".url(Web::sitemap->url())."\n",
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=utf-8'],
        );
    }
}
