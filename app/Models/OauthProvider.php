<?php

namespace App\Models;

use App\Sources\Db\App\OauthProviders;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $user_id
 * @property string $sub
 * @property string $name
 * @property string $given_name
 * @property string $family_name
 * @property string $picture
 * @property string $email
 * @property bool $email_verified
 * @property string|null $hd
 * @property string $id
 * @property bool $verified_email
 * @property string|null $link
 * @property-read User $user
 */
class OauthProvider extends Model
{
    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $primaryKey = OauthProviders::sub->value;

    /** @var string */
    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        OauthProviders::user_id->value,
        OauthProviders::sub->value,
        OauthProviders::name->value,
        OauthProviders::given_name->value,
        OauthProviders::family_name->value,
        OauthProviders::picture->value,
        OauthProviders::email->value,
        OauthProviders::email_verified->value,
        OauthProviders::hd->value,
        OauthProviders::id->value,
        OauthProviders::verified_email->value,
        OauthProviders::link->value,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            OauthProviders::email_verified->value => 'boolean',
            OauthProviders::verified_email->value => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
