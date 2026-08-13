<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Modules\Settings\Credentials\TokenForm;
use App\Routes\Auth;
use App\Sources\Db\App\PersonalAccessTokens;
use Illuminate\Support\Str;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\DbModel\ColumnType;

readonly class CredentialsTable
{
    use DataModel;

    public const string sessionKey = 'credential';

    public const string tokens = 'tokens';

    /** @var list<array<string, mixed>> */
    #[Describe([Describe::required => true])]
    public array $tokens;

    public const string issued = 'issued';

    #[Describe([Describe::default => [self::class, 'issuedToken']])]
    public ?string $issued;

    /** @return list<PersonalAccessTokens> */
    public static function columns(): array
    {
        return [
            PersonalAccessTokens::name,
            PersonalAccessTokens::abilities,
            PersonalAccessTokens::last_used_at,
            PersonalAccessTokens::expires_at,
            PersonalAccessTokens::created_at,
        ];
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        $headers = [];

        foreach (self::columns() as $Column) {
            $heading = $Column->type() === ColumnType::timestamp->value
                ? Str::before($Column->name, '_at')
                : $Column->name;

            $headers[Str::headline($heading)] = (string) $Column->comment();
        }

        return $headers;
    }

    /** @return list<CredentialRow> */
    public function rows(): array
    {
        return array_map(static fn (array $token): CredentialRow => CredentialRow::from($token), $this->tokens);
    }

    public function span(): int
    {
        return count(self::columns()) + 1;
    }

    public function action(): string
    {
        return Auth::settingsCredentials->url();
    }

    /** @return array<string, mixed> */
    public function nameInput(): array
    {
        return TokenForm::textInput(TokenForm::name);
    }

    /** @return array<string, mixed> */
    public function expiresAtInput(): array
    {
        return TokenForm::textInput(TokenForm::expires_at);
    }

    /** @param  array<string, mixed>  $context */
    public static function issuedToken(mixed $value, array $context): ?string
    {
        $token = session(self::sessionKey);

        return is_string($token) ? $token : null;
    }
}
