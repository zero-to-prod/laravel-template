<?php

namespace App\Modules\Api\User\Token\Store;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\PersonalAccessTokens;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/**
 * The created token, plus the one thing no other endpoint can return.
 *
 * `#[Describe(['nullable'])]` for the same reason it appears on
 * `UserTokenShowResponse`: without it a null field is absent rather than null.
 */
readonly class UserTokenStoreResponse
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

    public const string token = 'token';

    // Not PersonalAccessTokens::token->comment(): that column stores the
    // digest, while this carries the plain text, which is never stored and is
    // never returned again.
    #[Response([Response::description => 'The plain text token. Shown once, at creation, and not recoverable afterwards.'])]
    public string $token;

    public const string abilities = 'abilities';

    /** @var list<string>|null */
    #[Response([Response::schema => static function () {
        return [
            Schema::type => Schema::array,
            Schema::items => [Property::type => Property::string],
            Property::description => PersonalAccessTokens::abilities->comment(),
        ];
    }])]
    #[Describe(['nullable'])]
    public ?array $abilities;

    public const string expires_at = 'expires_at';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::expires_at->schema();
    }])]
    #[Describe(['nullable'])]
    public ?string $expires_at;

    public const string created_at = 'created_at';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::created_at->schema();
    }])]
    #[Describe(['nullable'])]
    public ?string $created_at;
}
