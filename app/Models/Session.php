<?php

namespace App\Models;

use App\Sources\Db\App\Sessions;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $payload
 * @property int $last_activity
 */
class Session extends Model
{
    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        Sessions::id->value,
        Sessions::user_id->value,
        Sessions::ip_address->value,
        Sessions::user_agent->value,
        Sessions::payload->value,
        Sessions::last_activity->value,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [Sessions::last_activity->value => 'integer'];
    }
}
