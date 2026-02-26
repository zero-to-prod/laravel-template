<?php

namespace App\Models;

use App\Models\Support\UserColumns;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use UserColumns;

    /** @var list<string> */
    protected $fillable = [
        self::name,
        self::email,
        self::password,
    ];

    /** @var list<string> */
    protected $hidden = [
        self::password,
        self::remember_token,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            self::email_verified_at => 'datetime',
            self::password => 'hashed',
        ];
    }

    public function matchesPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
