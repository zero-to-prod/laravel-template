<?php

namespace App\Models;

use App\Sources\Db\App\Users;
use Database\Factories\UserFactory;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasApiTokens<PersonalAccessToken> */
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasUlids;
    use Notifiable;

    /** @var list<string> */
    protected $fillable = [
        Users::name->value,
        Users::email->value,
        Users::password->value,
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
        ];
    }

    /** @throws AuthenticationException */
    public static function authenticated(Request $Request): self
    {
        $User = $Request->user();

        if (! $User instanceof self) {
            throw new AuthenticationException;
        }

        return $User;
    }

    public function matchesPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
