<?php

namespace App\Models;

use App\Models\Support\UserColumns;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use UserColumns;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

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
}
