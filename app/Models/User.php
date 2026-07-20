<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasUlids;
    use Notifiable;

    /** @var list<string> */
    protected $fillable = [
        \App\DataModels\User::name,
        \App\DataModels\User::email,
        \App\DataModels\User::password,
    ];

    /** @var list<string> */
    protected $hidden = [
        \App\DataModels\User::password,
        \App\DataModels\User::remember_token,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            \App\DataModels\User::email_verified_at => 'datetime',
            \App\DataModels\User::password => 'hashed',
        ];
    }

    public function matchesPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
