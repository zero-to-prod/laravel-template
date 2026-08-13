<?php

namespace App\Models;

use App\Sources\Db\App\Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property string $value
 * @property int $expiration
 */
class CacheEntry extends Model
{
    /** @var string */
    protected $primaryKey = Cache::key->value;

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        Cache::key->value,
        Cache::value->value,
        Cache::expiration->value,
    ];

    public function getTable(): string
    {
        return Cache::table();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            Cache::expiration->value => 'integer',
        ];
    }
}
