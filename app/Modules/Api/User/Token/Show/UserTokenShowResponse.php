<?php

namespace App\Modules\Api\User\Token\Show;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\PersonalAccessTokens;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/**
 * One personal access token, as every token endpoint publishes it.
 *
 * The secret is deliberately absent: `personal_access_tokens.token` holds a
 * SHA-256 digest, and the plain text exists only in the response that created
 * it.
 *
 * Every nullable field carries `#[Describe(['nullable'])]`. Without it the
 * property is left uninitialized, `get_object_vars()` skips it, and the field
 * is missing from the body rather than present and null — which the document
 * permits, and which no client asked for.
 */
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

    /**
     * Not `PersonalAccessTokens::abilities->schema()`: the column is `text`,
     * but the model casts it to json, so the wire type is a list of strings
     * and the column's own type would describe the storage instead.
     *
     * @var list<string>|null
     */
    #[Response([Response::schema => static function () {
        return [
            Schema::type => Schema::array,
            Schema::items => [Property::type => Property::string],
            Property::description => PersonalAccessTokens::abilities->comment(),
        ];
    }])]
    #[Describe(['nullable'])]
    public ?array $abilities;

    public const string last_used_at = 'last_used_at';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::last_used_at->schema();
    }])]
    #[Describe(['nullable'])]
    public ?string $last_used_at;

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

    public const string updated_at = 'updated_at';

    #[Response([Response::schema => static function () {
        return PersonalAccessTokens::updated_at->schema();
    }])]
    #[Describe(['nullable'])]
    public ?string $updated_at;
}
