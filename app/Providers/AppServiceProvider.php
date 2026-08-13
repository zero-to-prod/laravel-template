<?php

namespace App\Providers;

use App\Helpers\Theme;
use App\Models\PersonalAccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Head\Enums\Media;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        View::addLocation(dirname(__DIR__).'/View/Components');
        Model::preventLazyLoading();
        Model::preventAccessingMissingAttributes();
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    public function boot(): void
    {
        $name = Config::string('app.name');

        Head::defaults(static fn (HeadBuilder $head): HeadBuilder => $head
            ->title($name, suffix: " - $name")
            ->description('An opinionated Laravel template.')
            ->applicationName($name)
            ->canonical()
            ->viewport('width=device-width, initial-scale=1.0')
            ->colorScheme('light dark')
            ->referrer('strict-origin-when-cross-origin')
            ->themeColor(Theme::light->color(), media: Media::Light)
            ->themeColor(Theme::dark->color(), media: Media::Dark)
            ->og(type: OgType::Website, siteName: $name)
            ->twitter(card: TwitterCard::Summary)
            ->searchableByRobots());
    }
}
