<?php

namespace App\Modules\Admin\Users\Update;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Helpers\Theme;
use App\Sources\Db\App\Users;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Validation\Rules\Password;
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

    public const string theme = 'theme';

    #[Request([Request::rules => static function () {
        return [Rule::required, ValidationRule::enum(Theme::class)];
    }])]
    public string $theme;

    public const string password = 'password';

    #[Describe([Describe::default => ''])]
    #[Request([Request::rules => static function () {
        return [Rule::nullable, Rule::confirmed, Password::defaults()];
    }])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[Describe([Describe::default => ''])]
    public string $password_confirmation;
}
