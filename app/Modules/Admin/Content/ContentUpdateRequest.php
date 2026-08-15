<?php

namespace App\Modules\Admin\Content;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;

readonly class ContentUpdateRequest
{
    use DataModel;
    use IsRequest;

    public const string robots = 'robots';

    #[Request([Request::rules => [Rule::required, Rule::string, 'max:100000']])]
    public string $robots;

    public const string llms = 'llms';

    #[Request([Request::rules => [Rule::required, Rule::string, 'max:100000']])]
    public string $llms;

    public const string api_readme = 'api_readme';

    #[Request([Request::rules => [Rule::required, Rule::string, 'max:100000']])]
    public string $api_readme;
}
