<?php

namespace App\Modules\Api\User\Token\Show;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\PersonalAccessTokens;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

#[Describe([Describe::nullable => true])]
readonly class UserTokenShowResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string id = 'id';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::id->schema();
    }])]
    public int $id;

    public const string name = 'name';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::name->schema();
    }])]
    public string $name;

    public const string abilities = 'abilities';

    /** @var list<string>|null */
    #[Response([Response::schema => static function () {
        return [
            Schema::type => Schema::array,
            Schema::items => [Property::type => Property::string],
            Property::description => PersonalAccessTokens::abilities->comment(),
        ];
    }])]
    public ?array $abilities;

    public const string last_used_at = 'last_used_at';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::last_used_at->schema();
    }])]
    public ?string $last_used_at;

    public const string expires_at = 'expires_at';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::expires_at->schema();
    }])]
    public ?string $expires_at;

    public const string created_at = 'created_at';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::created_at->schema();
    }])]
    public ?string $created_at;

    public const string updated_at = 'updated_at';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::updated_at->schema();
    }])]
    public ?string $updated_at;
}
