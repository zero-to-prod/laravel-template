<?php

namespace App\Modules\Settings\Profile;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;

readonly class ProfileRequest
{
    use DataModel;
    use IsRequest;

    public const string name = 'name';

    #[Describe([Describe::cast => [self::class, 'sanitize']])]
    #[Request([Request::rules => static function () {
        return Users::name->rules();
    }])]
    public string $name;
}
