<?php

namespace App\Modules\Settings\Credentials;

use App\Helpers\DataModel;
use App\Helpers\DataModelCast;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Sources\Db\App\PersonalAccessTokens;
use Illuminate\Support\Carbon;
use Zerotoprod\DataModel\Describe;

readonly class TokenRequest
{
    use DataModel;
    use IsRequest;

    public const string name = 'name';

    #[Describe([Describe::cast => [self::class, 'sanitize']])]
    #[Request([Request::rules => static function () {
        return PersonalAccessTokens::name->rules();
    }])]
    public string $name;

    public const string expires_at = 'expires_at';

    #[Describe([Describe::cast => [DataModelCast::class, 'sanitizeNullable']])]
    #[Request([
        Request::rules => static function () {
            return [Rule::nullable, Rule::date, Rule::after('today')];
        },
        Request::attributes => 'expiration date',
    ])]
    public ?string $expires_at;

    public function expiresAt(): ?Carbon
    {
        return $this->expires_at === null ? null : Carbon::parse($this->expires_at);
    }
}
