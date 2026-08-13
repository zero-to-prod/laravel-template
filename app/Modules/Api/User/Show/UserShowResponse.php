<?php

namespace App\Modules\Api\User\Show;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;

#[Describe([Describe::nullable => true])]
readonly class UserShowResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string id = 'id';

    #[Response([Response::schema => static function () {
        return Users::id->schema();
    }])]
    public string $id;

    public const string name = 'name';

    #[Response([Response::schema => static function () {
        return Users::name->schema();
    }])]
    public string $name;

    public const string email = 'email';

    #[Response([Response::schema => static function () {
        return Users::email->schema();
    }])]
    public string $email;

    public const string email_verified_at = 'email_verified_at';

    #[Response([Response::schema => static function () {
        return Users::email_verified_at->schema();
    }])]
    public ?string $email_verified_at;

    public const string created_at = 'created_at';

    #[Response([Response::schema => static function () {
        return Users::created_at->schema();
    }])]
    public ?string $created_at;

    public const string updated_at = 'updated_at';

    #[Response([Response::schema => static function () {
        return Users::updated_at->schema();
    }])]
    public ?string $updated_at;
}
