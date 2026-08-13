<?php

namespace App\Modules\Admin\Users\Update;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;

readonly class UsersUpdateRequest
{
    use DataModel;
    use IsRequest;

    public const string name = 'name';

    #[Describe([Describe::cast => [self::class, 'sanitize']])]
    #[Request([Request::rules => static function () {
        return Users::name->rules();
    }])]
    public string $name;

    public const string email = 'email';

    #[Describe([Describe::cast => [self::class, 'sanitizeEmail']])]
    #[Request([Request::rules => static function () {
        return [
            ...Users::email->rules(),
            Rule::email,
        ];
    }])]
    public string $email;

    public const string verified = 'verified';

    #[Describe([Describe::default => false])]
    public bool $verified;

    public const string admin = 'admin';

    #[Describe([Describe::default => false])]
    public bool $admin;
}
