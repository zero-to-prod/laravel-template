<?php

namespace App\Modules\Api\User;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\Users;

readonly class ApiUserResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string id = 'id';

    #[Response([Response::description => static function () {
        return Users::id->comment();
    }])]
    public string $id;

    public const string name = 'name';

    #[Response([Response::description => static function () {
        return Users::name->comment();
    }])]
    public string $name;

    public const string email = 'email';

    #[Response([Response::description => static function () {
        return Users::email->comment();
    }])]
    public string $email;

    public const string email_verified_at = 'email_verified_at';

    #[Response([Response::description => static function () {
        return Users::email_verified_at->comment();
    }])]
    public ?string $email_verified_at;

    public const string created_at = 'created_at';

    #[Response([Response::description => static function () {
        return Users::created_at->comment();
    }])]
    public ?string $created_at;

    public const string updated_at = 'updated_at';

    #[Response([Response::description => static function () {
        return Users::updated_at->comment();
    }])]
    public ?string $updated_at;
}
