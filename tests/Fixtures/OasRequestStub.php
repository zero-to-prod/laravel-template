<?php

namespace Tests\Fixtures;

use App\DataModels\Fields\Request;
use App\Helpers\DataModel;
use App\Helpers\HasRequestSchema;
use App\Helpers\Oas\Checks\Confirmed;
use App\Helpers\Oas\Checks\Unique;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;

/**
 * Exercises every shape #[Request] accepts: a closure schema, a literal schema,
 * a closure description, a non-array schema, and ValueChecks.
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
        Request::checks => [new Unique('users', 'email')],
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

    public const string broken = 'broken';

    #[Request([
        Request::schema => 'not an array',
        Request::checks => 'not a list',
    ])]
    public string $broken;
}
