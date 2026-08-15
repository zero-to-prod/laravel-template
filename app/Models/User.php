<?php

namespace App\Models;

use App\Helpers\Theme;
use App\Sources\Db\App\Users;
use Database\Factories\UserFactory;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property Theme $theme
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, OauthProvider> $oauthProviders
 *
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasApiTokens<PersonalAccessToken> */
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use HasUlids;
    use Notifiable;

    /** @var list<string> */
    protected $fillable = [
        Users::name->value,
        Users::email->value,
        Users::password->value,
        Users::theme->value,
    ];

    /** @var array<string, string> */
    protected $attributes = [
        Users::theme->value => Theme::auto->value,
    ];

    /** @var list<string> */
    protected $hidden = [
        Users::password->value,
        Users::remember_token->value,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            Users::email_verified_at->value => 'datetime',
            Users::password->value => 'hashed',
            Users::theme->value => Theme::class,
        ];
    }

    public static function authenticated(Request $Request): self
    {
        $User = $Request->user();

        if (! $User instanceof self) {
            throw new AuthenticationException('Unauthenticated');
        }

        return $User;
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $Authenticated = Auth::user();

        if ($Authenticated instanceof self
            && ($field === null || $field === $Authenticated->getRouteKeyName())
            && $Authenticated->id === $value) {
            return $Authenticated;
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /** @return HasMany<OauthProvider, $this> */
    public function oauthProviders(): HasMany
    {
        return $this->hasMany(OauthProvider::class);
    }
}
