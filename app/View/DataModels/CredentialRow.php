<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Routes\Auth;
use App\Sources\Db\App\PersonalAccessTokens;
use Illuminate\Support\Carbon;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\DbModel\ColumnType;

#[Describe([Describe::nullable => true])]
readonly class CredentialRow
{
    use DataModel;

    public const string id = 'id';

    #[Describe([Describe::required => true])]
    public string $id;

    public const string name = 'name';

    #[Describe([Describe::required => true])]
    public string $name;

    public const string abilities = 'abilities';

    /** @var list<string>|null */
    public ?array $abilities;

    public const string last_used_at = 'last_used_at';

    public ?string $last_used_at;

    public const string expires_at = 'expires_at';

    public ?string $expires_at;

    public const string created_at = 'created_at';

    public ?string $created_at;

    public function revokeUrl(): string
    {
        return Auth::settingsCredential->url([Auth::credentialParameter => $this->id]);
    }

    public function expired(): bool
    {
        return $this->expires_at !== null && Carbon::parse($this->expires_at)->isPast();
    }

    /** @return list<string> */
    public function cells(): array
    {
        return array_map(fn (PersonalAccessTokens $Column): string => $this->cell($Column), CredentialsTable::columns());
    }

    public function cell(PersonalAccessTokens $PersonalAccessTokens): string
    {
        if ($PersonalAccessTokens === PersonalAccessTokens::abilities) {
            return $this->abilities === null || $this->abilities === []
                ? '—'
                : implode(', ', $this->abilities);
        }

        $value = $this->collect()->get($PersonalAccessTokens->value);

        return match (true) {
            ! is_string($value) || $value === '' => '—',
            $PersonalAccessTokens->type() === ColumnType::timestamp->value => Carbon::parse($value)->toFormattedDateString(),
            default => $value,
        };
    }
}
