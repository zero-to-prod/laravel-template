<?php

namespace App\Modules\Api\User\Token\Store;

use App\Helpers\DataModel;
use App\Helpers\Request;
use App\Modules\Api\Support\Future;
use App\Modules\Api\Support\HasRequestSchema;
use App\Sources\Db\App\PersonalAccessTokens;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

readonly class UserTokenStoreRequest
{
    use DataModel;
    use HasRequestSchema;

    /** @var list<string> */
    public const array all_abilities = ['*'];

    public const string name = 'name';

    #[Request([
        Request::schema => static function (): array {
            return [
                ...PersonalAccessTokens::name->schema(),
                Property::description => 'A label for the token, shown back to the user.',
            ];
        },
        Request::required => true,
    ])]
    public string $name;

    public const string abilities = 'abilities';

    /** @var list<string>|null */
    #[Request([
        Request::schema => static function (): array {
            return [
                Schema::type => Schema::array,
                Schema::items => [Property::type => Property::string, Property::maxLength => 255],
                Property::nullable => true,
                Property::description => 'The abilities to grant. Omitted grants `*`, which is every ability.',
            ];
        },
    ])]
    #[Describe(['nullable'])]
    public ?array $abilities;

    public const string expires_at = 'expires_at';

    #[Request([
        Request::schema => static function (): array {
            return [
                Property::type => Property::string,
                Property::format => Property::date_time,
                Property::nullable => true,
                Property::description => 'When the token stops being accepted. Omitted never expires.',
            ];
        },
        Request::checks => [new Future],
    ])]
    #[Describe(['nullable'])]
    public ?string $expires_at;

    /** @return list<string> */
    public function abilities(): array
    {
        return $this->abilities === null || $this->abilities === [] ? self::all_abilities : $this->abilities;
    }
}
