<?php

namespace App\Modules\Login;

use App\Helpers\DataModel;
use Illuminate\Support\Str;
use Zerotoprod\DataModel\Describe;

class GoogleUser
{
    use DataModel;

    public const string sub = 'sub';

    #[Describe([Describe::required => true])]
    public string $sub;

    public const string name = 'name';

    #[Describe([Describe::required => true])]
    public string $name;

    public const string given_name = 'given_name';

    #[Describe([Describe::required => true])]
    public string $given_name;

    public const string family_name = 'family_name';

    #[Describe([Describe::required => true])]
    public string $family_name;

    public const string picture = 'picture';

    #[Describe([Describe::required => true])]
    public string $picture;

    public const string email = 'email';

    #[Describe([
        Describe::required => true,
        Describe::cast => [self::class, 'castEmail'],
    ])]
    public string $email;

    public static function castEmail(string $value): string
    {
        return Str::lower(trim($value));
    }

    public const string email_verified = 'email_verified';

    #[Describe([Describe::required => true])]
    public bool $email_verified;

    public const string hd = 'hd';

    #[Describe([Describe::default => null])]
    public ?string $hd = null;

    public const string id = 'id';

    #[Describe([Describe::required => true])]
    public string $id;

    public const string verified_email = 'verified_email';

    #[Describe([Describe::required => true])]
    public bool $verified_email;

    public const string link = 'link';

    #[Describe([Describe::default => null])]
    public ?string $link = null;

    public function hasVerifiedEmail(): bool
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false
            && $this->email_verified
            && $this->verified_email;
    }
}
