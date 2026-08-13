<?php

namespace App\Modules\Settings\Appearance;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Helpers\Theme;
use Illuminate\Validation\Rules\Enum;

readonly class AppearanceRequest
{
    use DataModel;
    use IsRequest;

    public const string theme = 'theme';

    #[Request([Request::rules => static function () {
        return [
            Rule::required,
            new Enum(Theme::class),
        ];
    }])]
    public string $theme;
}
