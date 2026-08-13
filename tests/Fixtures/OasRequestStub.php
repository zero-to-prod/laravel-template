<?php

namespace Tests\Fixtures;

use App\Helpers\DataModel;
use App\Helpers\Request;
use App\Models\User;
use App\Modules\Api\Support\Confirmed;
use App\Modules\Api\Support\Future;
use App\Modules\Api\Support\HasRequestSchema;
use App\Modules\Api\Support\Unique;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;

/**
 * Exercises every shape a declared request property accepts: a deferred schema, a
 * literal one, a deferred description, something that is not a schema at all, and
 * the checks that run after it.
 */
readonly class OasRequestStub
{
    use DataModel;
    use HasRequestSchema;

    public const string email = 'email';

    #[Request([
        Request::schema => static function (): array {
            return [Property::type => Property::string, Property::minLength => 1];
        },
        Request::required => true,
        Request::checks => [new Unique(User::class, Users::email->value)],
    ])]
    public string $email;

    public const string password = 'password';

    #[Request([
        Request::schema => [Property::type => Property::string],
        Request::required => true,
        Request::checks => [new Confirmed, 'not a check'],
    ])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[Describe([Describe::default => ''])]
    public string $password_confirmation;

    public const string nickname = 'nickname';

    #[Request([
        Request::schema => [Property::type => Property::string],
        Request::description => static function (): string {
            return Users::email->comment() ?? '';
        },
    ])]
    public string $nickname;

    public const string expires_at = 'expires_at';

    #[Request([
        Request::schema => [Property::type => Property::string, Property::format => Property::date_time],
        Request::checks => [new Future],
    ])]
    public string $expires_at;

    public const string broken = 'broken';

    #[Request([
        Request::schema => 'not an array',
        Request::checks => 'not a list',
    ])]
    public string $broken;
}
