<?php

namespace App\Models;

use App\Sources\Db\App\CacheLocks;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property string $owner
 * @property int $expiration
 */
class CacheLock extends Model
{
    /** @var string */
    protected $primaryKey = CacheLocks::key->value;

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        CacheLocks::key->value,
        CacheLocks::owner->value,
        CacheLocks::expiration->value,
    ];

    public function getTable(): string
    {
        return CacheLocks::table();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            CacheLocks::expiration->value => 'integer',
        ];
    }
}
